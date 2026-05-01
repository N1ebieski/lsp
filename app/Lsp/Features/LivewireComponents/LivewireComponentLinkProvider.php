<?php

declare(strict_types=1);

namespace App\Lsp\Features\LivewireComponents;

use App\Lsp\Contracts\LinkProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class LivewireComponentLinkProvider implements LinkProvider
{
    /**
     * Create a new Livewire component link provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide Livewire component links for the given document.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document): array
    {
        if (! $this->workspace->config->boolean('livewireComponentLink', true)) {
            return [];
        }

        return (new LivewireComponentDocumentMapper($this->workspace))->links($document);
    }
}
