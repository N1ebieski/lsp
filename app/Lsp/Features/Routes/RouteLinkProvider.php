<?php

declare(strict_types=1);

namespace App\Lsp\Features\Routes;

use App\Lsp\Contracts\LinkProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class RouteLinkProvider implements LinkProvider
{
    /**
     * Create a new route link provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide route document links for the given document.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document): array
    {
        if (! $this->workspace->config->boolean('routeLink', true)) {
            return [];
        }

        return (new RouteDocumentMapper(
            $this->workspace,
            $this->workspace->data->routes()->get()->keyBy('name'),
        ))->links($document);
    }
}
