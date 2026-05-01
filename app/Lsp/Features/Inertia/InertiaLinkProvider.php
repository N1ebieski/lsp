<?php

declare(strict_types=1);

namespace App\Lsp\Features\Inertia;

use App\Lsp\Contracts\LinkProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class InertiaLinkProvider implements LinkProvider
{
    /**
     * Create a new Inertia link provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide Inertia document links for the given document.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document): array
    {
        if (! $this->workspace->config->boolean('inertiaLink', true)) {
            return [];
        }

        return (new InertiaDocumentMapper(
            $this->workspace,
            $this->workspace->data->inertiaViews()->get(),
        ))->links($document);
    }
}
