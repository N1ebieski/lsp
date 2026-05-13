<?php

declare(strict_types=1);

namespace App\Lsp\Listeners;

use App\Lsp\Contracts\Listener;
use App\Lsp\Support\Pattern;
use App\Lsp\Support\Uri;
use App\Lsp\Transport\JsonRpcRequest;
use App\Lsp\Workspace;

class NotifyFileWatchers implements Listener
{
    /**
     * Handle the workspace/didChangeWatchedFiles notification.
     */
    public function handle(JsonRpcRequest $request, Workspace $workspace): void
    {
        $paths = $this->paths($request, $workspace);

        if ($paths === []) {
            return;
        }

        foreach ($workspace->features->watchers() as $watcher) {
            if (Pattern::matchesAnyPath($paths, $watcher->patterns())) {
                $watcher->onFileChange($paths);
            }
        }
    }

    /**
     * Get changed workspace-relative paths.
     *
     * @return array<int, string>
     */
    protected function paths(JsonRpcRequest $request, Workspace $workspace): array
    {
        return $request->collect('changes')
            ->pluck('uri')
            ->filter(fn (mixed $uri): bool => is_string($uri))
            ->map(fn (string $uri): ?string => $this->relativePathFromUri($uri, $workspace))
            ->filter(fn (mixed $path): bool => is_string($path))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Get a workspace-relative path from a file URI.
     */
    protected function relativePathFromUri(string $uri, Workspace $workspace): ?string
    {
        if (parse_url($uri, PHP_URL_SCHEME) !== 'file') {
            return null;
        }

        $path = Uri::of($uri)->path();

        if ($path === '') {
            return null;
        }

        $relativePath = Uri::of($workspace->baseUri)->relativePath($path);

        return $relativePath === $path ? null : $relativePath;
    }
}
