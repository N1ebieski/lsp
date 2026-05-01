<?php

declare(strict_types=1);

namespace App\Lsp\Features\Routes;

use App\Lsp\Contracts\CompletionProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class RouteCompletionProvider implements CompletionProvider
{
    /**
     * Create a new route completion provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide route completions for the given document and position.
     *
     * @param  array<string, mixed>  $position
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document, array $position): array
    {
        if (! $this->workspace->config->boolean('routeCompletion', true)) {
            return [];
        }

        return (new RouteDocumentMapper(
            $this->workspace,
            $this->workspace->data->routes()->get()->keyBy('name'),
        ))->completions($document, $position);
    }
}
