<?php

declare(strict_types=1);

namespace App\Lsp\Features\Routes;

use App\Lsp\Contracts\HoverProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class RouteHoverProvider implements HoverProvider
{
    /**
     * Create a new route hover provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide route hover for the given document and position.
     *
     * @param  array<string, mixed>  $position
     * @return array<string, mixed>|null
     */
    public function get(Document $document, array $position): ?array
    {
        if (!$this->workspace->config->boolean('routeHover', true)) {
            return null;
        }

        return (new RouteDocumentMapper($this->workspace))->hover($document, $position);
    }
}
