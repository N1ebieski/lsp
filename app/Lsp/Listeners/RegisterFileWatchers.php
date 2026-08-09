<?php

declare(strict_types=1);

namespace App\Lsp\Listeners;

use App\Lsp\Contracts\Listener;
use App\Lsp\FeatureRegistry;
use App\Lsp\Project;
use App\Lsp\Projects;
use App\Lsp\Server;
use App\Lsp\Transport\JsonRpcRequest;

class RegisterFileWatchers implements Listener
{
        /**
     * Instantiate a new class instance.
     */
    public function __construct(
        protected Server $server,
        protected FeatureRegistry $features,
        protected Projects $projects,
    ) {}

    /**
     * Handle the incoming LSP notification.
     */
    public function handle(JsonRpcRequest $request): void
    {
        $this->server->send('client/registerCapability', [
            'registrations' => [
                [
                    'id' => 'file-watching',
                    'method' => 'workspace/didChangeWatchedFiles',
                    'registerOptions' => [
                        'watchers' => $this->projects
                            ->collect()
                            ->flatMap($this->watchers(...))
                            ->values()
                            ->all(),
                    ],
                ],
            ],
        ]);
    }

    protected function watchers(Project $project): array
    {
        return array_map(fn (string $pattern) => [
            'globPattern' => [
                'baseUri' => $project->uri,
                'pattern' => $pattern,
            ],
            'kind' => 7,
        ], $this->patterns());
    }

    /**
     * Collect watcher patterns.
     */
    protected function patterns(): array
    {
        $patterns = [];

        foreach ($this->features->watchers() as $watcher) {
            array_push($patterns, ...$watcher->patterns());
        }

        return array_values(array_unique($patterns));
    }
}
