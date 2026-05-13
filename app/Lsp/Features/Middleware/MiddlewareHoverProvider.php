<?php

declare(strict_types=1);

namespace App\Lsp\Features\Middleware;

use App\Lsp\Contracts\HoverProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class MiddlewareHoverProvider implements HoverProvider
{
    /**
     * Create a new middleware hover provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {}

    /**
     * Provide middleware hover for the given document and position.
     *
     * @param  array<string, mixed>  $position
     * @return array<string, mixed>|null
     */
    public function get(Document $document, array $position): ?array
    {
        if (!$this->workspace->config->boolean('middlewareHover', true)) {
            return null;
        }

        return (new MiddlewareDocumentMapper($this->workspace))->hover($document, $position);
    }
}
