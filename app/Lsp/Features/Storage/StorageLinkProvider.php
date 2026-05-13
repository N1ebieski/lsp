<?php

declare(strict_types=1);

namespace App\Lsp\Features\Storage;

use App\Lsp\Contracts\LinkProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class StorageLinkProvider implements LinkProvider
{
    /**
     * Create a new storage link provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide storage disk links for the given document.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document): array
    {
        if (!$this->workspace->config->boolean('storageLink', true)) {
            return [];
        }

        return (new StorageDocumentMapper($this->workspace))->links($document);
    }
}
