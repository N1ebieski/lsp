<?php

declare(strict_types=1);

namespace App\Lsp\Features\Storage;

use App\Lsp\Contracts\CompletionProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class StorageCompletionProvider implements CompletionProvider
{
    /**
     * Create a new storage completion provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide storage disk completions for the given document and position.
     *
     * @param  array<string, mixed>  $position
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document, array $position): array
    {
        if (!$this->workspace->config->boolean('storageCompletion', true)) {
            return [];
        }

        return (new StorageDocumentMapper($this->workspace))->completions($document, $position);
    }
}
