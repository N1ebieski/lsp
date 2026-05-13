<?php

declare(strict_types=1);

namespace App\Lsp\Features\Storage;

use App\Lsp\Contracts\DiagnosticProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class StorageDiagnosticProvider implements DiagnosticProvider
{
    /**
     * Create a new storage diagnostic provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide storage disk diagnostics for the given document.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document): array
    {
        if (!$this->workspace->config->boolean('storageDiagnostics', true)) {
            return [];
        }

        return (new StorageDocumentMapper($this->workspace))->diagnostics($document);
    }
}
