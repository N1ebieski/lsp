<?php

declare(strict_types=1);

namespace App\Lsp\Features\Inertia;

use App\Lsp\Contracts\HoverProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class InertiaHoverProvider implements HoverProvider
{
    /**
     * Create a new Inertia hover provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide Inertia hover for the given document and position.
     *
     * @param  array<string, mixed>  $position
     * @return array<string, mixed>|null
     */
    public function get(Document $document, array $position): ?array
    {
        if (!$this->workspace->config->boolean('inertiaHover', true)) {
            return null;
        }

        return (new InertiaDocumentMapper($this->workspace))->hover($document, $position);
    }
}
