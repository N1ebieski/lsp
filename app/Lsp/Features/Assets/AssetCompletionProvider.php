<?php

declare(strict_types=1);

namespace App\Lsp\Features\Assets;

use App\Lsp\Contracts\CompletionProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class AssetCompletionProvider implements CompletionProvider
{
    /**
     * Create a new asset completion provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {}

    /**
     * Provide asset completions.
     *
     * @param  array<string, mixed>  $position
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document, array $position): array
    {
        if (!$this->workspace->config->boolean('assetCompletion', true)) {
            return [];
        }

        return (new AssetDocumentMapper($this->workspace))->completions($document, $position);
    }
}
