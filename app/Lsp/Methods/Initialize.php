<?php

declare(strict_types=1);

namespace App\Lsp\Methods;

use App\Lsp\Contracts\ExceptionHandler;
use App\Lsp\Contracts\Method;
use App\Lsp\Exceptions\ProjectNotFoundException;
use App\Lsp\PhpCommandDetector;
use App\Lsp\Project;
use App\Lsp\ProjectContext;
use App\Lsp\ProjectIndex;
use App\Lsp\Projects;
use App\Lsp\ScriptRunner;
use App\Lsp\Support\FileUri;
use App\Lsp\Transport\JsonRpcRequest;
use App\Lsp\Transport\JsonRpcResponse;
use Illuminate\Container\Container;
use Psr\Log\LoggerInterface;

final class Initialize implements Method
{
    /**
     * Instantiate a new class instance.
     */
    public function __construct(
        protected Container $container,
        protected LoggerInterface $logger,
        protected ProjectContext $context,
    ) {}

    /**
     * Handle the incoming LSP request.
     */
    public function handle(JsonRpcRequest $request): JsonRpcResponse
    {
        $this->container->singleton(Projects::class);

        $projects = [];

        $workspaceFolders = $request->array('workspaceFolders') ?? [
            ['uri' => $request->get('rootUri')]
        ];

        foreach ($workspaceFolders as $workspaceFolder) {
            $rootUri = $workspaceFolder['uri'] ?? null;

            if (!is_string($rootUri) || $rootUri === '') {
                continue;
            }

            $uri = FileUri::of($rootUri);

            if (!file_exists($uri->path('artisan'))) {
                continue;
            }

            $projects[] = new Project(
                $uri,
                $request->array('initializationOptions'),
                new ProjectIndex($this->container),
                new ScriptRunner($uri->path(), $this->phpCommand($request, $uri)),
            );
        }

        if ($projects === []) {
            return JsonRpcResponse::error($request->id(), -32602, 'Initialize request must include at least one Laravel project.');
        }

        $projects = new Projects(...$projects);

        $this->container->instance(Projects::class, $projects);

        $project = $projects->first();

        $this->context->setDefault($project);

        $this->logger->info('Initialized Laravel LSP.', [
            'rootUri'               => (string) $project->uri,
            'processId'             => $request->get('processId'),
            'clientInfo'            => $request->array('clientInfo'),
            'initializationOptions' => $project->all(),
            'phpEnvironment'        => $project->phpEnvironment(),
            'phpCommand'            => $project->scripts->command(),
        ]);

        return JsonRpcResponse::result($request->id(), [
            'capabilities' => [
                'textDocumentSync' => [
                    'openClose' => true,
                    'change'    => 1,
                ],
                'documentLinkProvider' => [
                    'resolveProvider' => false,
                ],
                'completionProvider' => [
                    'triggerCharacters' => ['"', "'", '.', '|', 'x', '-', ':', '@'],
                ],
                'codeActionProvider' => [
                    'codeActionKinds' => ['quickfix'],
                ],
                'definitionProvider' => $project->boolean('definitionProvider', true),
                'hoverProvider'      => true,
            ],
            'serverInfo' => [
                'name'    => 'Laravel LSP',
                'version' => (string) config('app.version'),
            ],
            'laravel' => [
                'phpEnvironment' => $project->phpEnvironment(),
                'phpCommand'     => $project->scripts->command(),
            ],
        ]);
    }

    /**
     * Resolve the php command.
     *
     * @return array<int, string>
     */
    protected function phpCommand(JsonRpcRequest $request, FileUri $uri): array
    {
        if ($command = $request->array('initializationOptions.phpCommand')) {
            return $command;
        }

        return (new PhpCommandDetector(
            $uri->path(),
            (string) $request->string('initializationOptions.phpEnvironment', 'auto'),
            $this->container[ExceptionHandler::class],
        ))->detect();
    }
}
