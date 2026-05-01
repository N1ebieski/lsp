<?php

declare(strict_types=1);

namespace App\Lsp;

use App\Lsp\Contracts\Listener;
use App\Lsp\Contracts\Method;
use App\Lsp\Contracts\Transport;
use App\Lsp\Listeners\ClearDocumentDiagnostics;
use App\Lsp\Listeners\CloseDocument;
use App\Lsp\Listeners\InvalidateWorkspaceData;
use App\Lsp\Listeners\OpenDocument;
use App\Lsp\Listeners\PublishDiagnostics;
use App\Lsp\Listeners\UpdateDocument;
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
        'workspace/didChangeWatchedFiles' => [InvalidateWorkspaceData::class],
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
                info('LSP incoming parse error.');

                $this->send(
                    JsonRpcResponse::error(null, -32700, 'Parse error: Invalid JSON.')->toJson()
                );

                return;
            }

            $this->logIncoming($jsonRequest);

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

            $this->send(
                JsonRpcResponse::error(
                    $jsonRequest['id'] ?? null,
                    -32603,
                    $e->getMessage(),
                )->toJson()
            );
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
        if ($workspace = Workspace::fromInitializeRequest($request, $this)) {
            $this->setWorkspace($workspace);
        }

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
                    'triggerCharacters' => ['"', "'"],
                ],
                'definitionProvider' => true,
                'hoverProvider'      => true,
            ],
            'serverInfo' => [
                'name'    => 'Laravel LSP',
                'version' => '0.1.0',
            ],
        ])->toJson());
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

        $this->sendRequest('client/registerCapability', [
            'registrations' => [
                [
                    'id'              => 'file-watching',
                    'method'          => 'workspace/didChangeWatchedFiles',
                    'registerOptions' => [
                        'watchers' => $watchers,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Handle the shutdown request.
     */
    protected function handleShutdown(JsonRpcRequest $request): void
    {
        $this->shutdown = true;

        $this->send(JsonRpcResponse::result($request->id(), [])->toJson());
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
            $this->send($response->toJson());
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
            )->toJson()
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

        $this->send(
            JsonRpcResponse::error(
                $request->id(),
                -32002,
                'Server not initialized.',
            )->toJson()
        );
    }

    /**
     * Determine if a workspace has been initialized.
     */
    public function hasWorkspace(): bool
    {
        return $this->workspace !== null;
    }

    /**
     * Get the active workspace instance.
     */
    public function workspace(): ?Workspace
    {
        return $this->workspace;
    }

    /**
     * Set the active workspace instance.
     */
    public function setWorkspace(Workspace $workspace): void
    {
        $this->workspace = $workspace;
    }

    /**
     * Send a JSON-RPC request to the client.
     *
     * @param  array<string, mixed>  $params
     */
    public function sendRequest(string $method, array $params = []): void
    {
        $message = json_encode([
            'jsonrpc' => '2.0',
            'id'      => $this->nextRequestId(),
            'method'  => $method,
            'params'  => $params,
        ], JSON_UNESCAPED_UNICODE);

        if ($message !== false) {
            $this->send($message);
        }
    }

    /**
     * Send a JSON-RPC notification to the client.
     *
     * @param  array<string, mixed>  $params
     */
    public function sendNotification(string $method, array $params = []): void
    {
        $this->send(
            JsonRpcResponse::notification($method, $params)->toJson()
        );
    }

    /**
     * Send a JSON-RPC message to the client.
     */
    protected function send(string $message): void
    {
        $this->logOutgoing($message);

        $this->transport->send($message);
    }

    /**
     * Log incoming JSON-RPC message metadata.
     *
     * @param  array<string, mixed>  $message
     */
    protected function logIncoming(array $message): void
    {
        return;

        info('LSP incoming message.', [
            'id'           => $message['id'] ?? null,
            'method'       => $message['method'] ?? null,
            'notification' => !array_key_exists('id', $message),
            'response'     => isset($message['id']) && !isset($message['method']),
            'error'        => isset($message['error']),
        ]);
    }

    /**
     * Log outgoing JSON-RPC message metadata.
     */
    protected function logOutgoing(string $message): void
    {
        return;

        $jsonMessage = json_decode($message, true);

        if (!is_array($jsonMessage)) {
            info('LSP outgoing message.', [
                'invalid' => true,
            ]);

            return;
        }

        info('LSP outgoing message.', [
            'id'           => $jsonMessage['id'] ?? null,
            'method'       => $jsonMessage['method'] ?? null,
            'notification' => isset($jsonMessage['method']) && !array_key_exists('id', $jsonMessage),
            'response'     => isset($jsonMessage['id']) && !isset($jsonMessage['method']),
            'error'        => isset($jsonMessage['error']),
        ]);
    }

    /**
     * Generate the next server-originated request ID.
     */
    protected function nextRequestId(): int
    {
        return $this->nextRequestId++;
    }
}
