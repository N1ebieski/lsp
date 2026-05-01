<?php

declare(strict_types=1);

namespace App\Lsp;

use App\Lsp\Support\Uri;
use App\Lsp\Transport\JsonRpcRequest;

class Workspace
{
    /**
     * The base URI for the workspace.
     */
    public readonly string $baseUri;

    /**
     * The decoded base path for the workspace.
     */
    public readonly string $basePath;

    /**
     * The document manager for the workspace.
     */
    public readonly DocumentManager $documents;

    /**
     * The PHP runner for the workspace.
     */
    public readonly PhpRunner $php;

    /**
     * The workspace data providers.
     */
    public readonly WorkspaceData $data;

    /**
     * The workspace configuration.
     */
    public readonly WorkspaceConfiguration $config;

    /**
     * The feature registry.
     */
    public readonly FeatureRegistry $features;

    /**
     * Create a new workspace instance.
     */
    public function __construct(
        string $baseUri,
        public readonly Server $server,
    ) {
        $this->baseUri = $baseUri;
        $this->basePath = Uri::of($baseUri)->path();

        $this->documents = new DocumentManager;
        $this->php = new PhpRunner($this->basePath);
        $this->data = new WorkspaceData($this);
        $this->config = new WorkspaceConfiguration;
        $this->features = new FeatureRegistry($this);
    }

    /**
     * Get the path to the workspace root.
     */
    public function path(string $path = ''): string
    {
        return Uri::of($this->baseUri)->path($path);
    }

    /**
     * Get the file URI to the workspace root.
     */
    public function uri(): Uri
    {
        return Uri::of($this->baseUri);
    }

    /**
     * Get a file target URI for a workspace-relative path.
     */
    public function target(string $relativePath, ?int $line = null): string
    {
        $target = (string) $this->uri()->joinPath($relativePath);

        if ($line !== null) {
            $target .= '#L'.max(1, $line);
        }

        return $target;
    }

    /**
     * Create a document link response for a workspace-relative path.
     *
     * @param  array<string, array<string, int>>  $range
     * @return array<string, mixed>
     */
    public function link(array $range, string $relativePath, ?int $line = null): array
    {
        return [
            'range' => $range,
            'target' => $this->target($relativePath, $line),
        ];
    }

    /**
     * Get file watchers for all providers.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fileWatchers(): array
    {
        $watchers = [];

        foreach ($this->data->all() as $provider) {
            foreach ($provider->patterns() as $pattern) {
                $watchers[] = [
                    'globPattern' => $pattern,
                    'kind'        => 7,
                ];
            }
        }

        return $watchers;
    }

    /**
     * Create a workspace from the initialize request.
     */
    public static function fromInitializeRequest(JsonRpcRequest $request, Server $server): ?self
    {
        $rootUri = $request->get('rootUri');

        if (!is_string($rootUri)) {
            return null;
        }

        $workspace = new self($rootUri, $server);

        $workspace->config->replace(
            $request->collect('initializationOptions')->all()
        );

        return $workspace;
    }
}
