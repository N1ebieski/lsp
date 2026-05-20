<?php

declare(strict_types=1);

namespace App\Lsp;

use App\Lsp\Contracts\Listener;
use App\Lsp\Contracts\Method;
use App\Lsp\Contracts\Transport;
use App\Lsp\Listeners\ClearDocumentDiagnostics;
use App\Lsp\Listeners\CloseDocument;
use App\Lsp\Listeners\NotifyFileWatchers;
use App\Lsp\Listeners\OpenDocument;
use App\Lsp\Listeners\PublishDiagnostics;
use App\Lsp\Listeners\PublishOpenDocumentDiagnostics;
use App\Lsp\Listeners\UpdateDocument;
use App\Lsp\Methods\LaravelData;
use App\Lsp\Methods\TextDocumentCodeAction;
use App\Lsp\Methods\TextDocumentCompletion;
use App\Lsp\Methods\TextDocumentDefinition;
use App\Lsp\Methods\TextDocumentDocumentLink;
use App\Lsp\Methods\TextDocumentHover;
use App\Lsp\Transport\JsonRpcRequest;
use App\Lsp\Transport\JsonRpcResponse;
use Throwable;

class Server
{
    /**
     * The registered request handlers.
     *
     * @var array<string, class-string<Method>>
     */
    protected array $requests = [
        'laravel/data'              => LaravelData::class,
        'textDocument/codeAction'   => TextDocumentCodeAction::class,
        'textDocument/completion'   => TextDocumentCompletion::class,
        'textDocument/definition'   => TextDocumentDefinition::class,
        'textDocument/documentLink' => TextDocumentDocumentLink::class,
        'textDocument/hover'        => TextDocumentHover::class,
    ];

    /**
     * The registered notification listeners.
     *
     * @var array<string, array<int, class-string<Listener>>>
     */
    protected array $notifications = [
        'textDocument/didOpen'            => [OpenDocument::class, PublishDiagnostics::class],
        'textDocument/didChange'          => [UpdateDocument::class, PublishDiagnostics::class],
        'textDocument/didClose'           => [CloseDocument::class, ClearDocumentDiagnostics::class],
        'workspace/didChangeWatchedFiles' => [NotifyFileWatchers::class, PublishOpenDocumentDiagnostics::class],
    ];

    /**
     * The active workspace instance.
     */
    protected ?Workspace $workspace = null;

    /**
     * The next request ID for server-originated requests.
     */
    protected int $nextRequestId = 1;

    /**
     * Indicates if the server has received a shutdown request.
     */
    protected bool $shutdown = false;

    /**
     * Create a new server instance.
     */
    public function __construct(
        protected Transport $transport,
    ) {}

    /**
     * Start the server and begin listening for messages.
     */
    public function start(): void
    {
        $this->transport->onReceive($this->handle(...));
        $this->transport->run();
    }

    /**
     * Handle an incoming raw JSON-RPC message.
     */
    public function handle(string $rawMessage): void
    {
        try {
            $jsonRequest = json_decode($rawMessage, true);

            if (!is_array($jsonRequest) || json_last_error() !== JSON_ERROR_NONE) {
                $this->send(JsonRpcResponse::error(null, -32700, 'Parse error: Invalid JSON.'));

                return;
            }

            if ($this->isResponse($jsonRequest)) {
                return;
            }

            $request = JsonRpcRequest::from($jsonRequest);

            $method = $request->method();

            if ($method === 'exit') {
                exit($this->shutdown ? 0 : 1);
            }

            if ($method === 'initialize') {
                $this->handleInitialize($request);

                return;
            }

            if ($method === 'initialized') {
                $this->handleInitialized();

                return;
            }

            if ($method === 'shutdown') {
                $this->handleShutdown($request);

                return;
            }

            if (isset($this->requests[$method])) {
                $this->handleRequest($request);

                return;
            }

            if (isset($this->notifications[$method])) {
                $this->handleNotification($request);

                return;
            }

            $this->handleUnknownRequest($request);
        } catch (Throwable $e) {
            report($e);

            $this->send(JsonRpcResponse::error(
                isset($jsonRequest) && is_array($jsonRequest) ? ($jsonRequest['id'] ?? null) : null,
                -32603,
                $e->getMessage(),
            ));
        }
    }

    /**
     * Determine if the given JSON-RPC payload is a response to a server-originated request.
     *
     * @param  array<string, mixed>  $jsonRequest
     */
    protected function isResponse(array $jsonRequest): bool
    {
        return isset($jsonRequest['id']) && !isset($jsonRequest['method']);
    }

    /**
     * Handle the initialize request and return server capabilities.
     */
    protected function handleInitialize(JsonRpcRequest $request): void
    {
        $this->workspace = new Workspace(
            $request->get('rootUri'),
            $this,
            new WorkspaceConfiguration($request->collect('initializationOptions')->all()),
        );

        $composer = json_decode(file_get_contents(__DIR__.'/../../composer.json'), true);

        $this->send(JsonRpcResponse::result($request->id(), [
            'capabilities' => [
                'textDocumentSync' => [
                    'openClose' => true,
                    'change'    => 1, // Full sync
                ],
                'documentLinkProvider' => [
                    'resolveProvider' => false,
                ],
                'completionProvider' => [
                    'triggerCharacters' => ['"', "'", '|', 'x', '-', ':', '@'],
                ],
                'codeActionProvider' => [
                    'codeActionKinds' => ['quickfix'],
                ],
                'definitionProvider' => $request->boolean('initializationOptions.definitionProvider', false),
                'hoverProvider'      => true,
            ],
            'serverInfo' => $info = [
                'name'    => 'Laravel LSP',
                'version' => $composer['version'],
            ],
            'laravel' => $laravel = [
                'phpEnvironment' => $this->workspace->config->phpEnvironment(),
                'phpCommand' => $this->workspace->php->command() ?? ['php'],
            ],
        ]));

        info('LSP Server Started.', array_merge($info, $laravel));
    }

    /**
     * Handle the initialized notification.
     */
    protected function handleInitialized(): void
    {
        $watchers = $this->workspace?->fileWatchers() ?? [];

        if ($watchers === []) {
            return;
        }

        $this->send([
            'id'     => $this->nextRequestId(),
            'method' => 'client/registerCapability',
            'params' => [
                'registrations' => [
                    [
                        'id'              => 'file-watching',
                        'method'          => 'workspace/didChangeWatchedFiles',
                        'registerOptions' => [
                            'watchers' => $watchers,
                        ],
                    ],
                ],
            ],
        ]);

        foreach ($this->workspace?->features->watchers() ?? [] as $watcher) {
            $watcher->initialize();
        }
    }

    /**
     * Handle the shutdown request.
     */
    protected function handleShutdown(JsonRpcRequest $request): void
    {
        $this->shutdown = true;

        $this->send(JsonRpcResponse::result($request->id(), []));
    }

    /**
     * Handle a request method that requires a workspace.
     */
    protected function handleRequest(JsonRpcRequest $request): void
    {
        if ($this->workspace === null) {
            $this->respondWorkspaceRequired($request);

            return;
        }

        $handler = new $this->requests[$request->method()];

        $response = $handler->handle($request, $this->workspace);

        if (!$request->isNotification()) {
            $this->send($response);
        }
    }

    /**
     * Handle a notification listener that requires a workspace.
     */
    protected function handleNotification(JsonRpcRequest $request): void
    {
        $workspace = $this->workspace;

        if ($workspace === null) {
            return;
        }

        foreach ($this->notifications[$request->method()] as $listener) {
            (new $listener)->handle($request, $workspace);
        }
    }

    /**
     * Handle an unknown request.
     */
    protected function handleUnknownRequest(JsonRpcRequest $request): void
    {
        if ($request->isNotification()) {
            return;
        }

        $this->send(
            JsonRpcResponse::error(
                $request->id(),
                -32601,
                "The method [{$request->method()}] was not found.",
            )
        );
    }

    /**
     * Send a workspace-required error response.
     */
    protected function respondWorkspaceRequired(JsonRpcRequest $request): void
    {
        if ($request->isNotification()) {
            return;
        }

        $this->send(JsonRpcResponse::error(
            $request->id(),
            -32002,
            'Server not initialized.',
        ));
    }

    /**
     * Send a JSON-RPC notification to the client.
     *
     * @param  array<string, mixed>  $params
     */
    public function sendNotification(string $method, array $params = []): void
    {
        $this->send(JsonRpcResponse::notification($method, $params));
    }

    /**
     * Send a JSON-RPC message to the client.
     *
     * @param  JsonRpcResponse|array<string, mixed>  $message
     */
    protected function send(JsonRpcResponse|array $message): void
    {
        $payload = $message instanceof JsonRpcResponse
            ? $message->toJson()
            : json_encode(['jsonrpc' => '2.0', ...$message], JSON_UNESCAPED_UNICODE);

        if ($payload !== false) {
            $this->transport->send($payload);
        }
    }

    /**
     * Generate the next server-originated request ID.
     */
    protected function nextRequestId(): int
    {
        return $this->nextRequestId++;
    }
}
