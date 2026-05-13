<?php

declare(strict_types=1);

namespace App\Lsp\Features\Middleware;

use App\Lsp\Contracts\LinkProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class MiddlewareLinkProvider implements LinkProvider
{
    /**
     * Create a new middleware link provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide middleware links for the given document.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document): array
    {
        if (!$this->workspace->config->boolean('middlewareLink', true)) {
            return [];
        }

        return (new MiddlewareDocumentMapper($this->workspace))->links($document);
    }
}
