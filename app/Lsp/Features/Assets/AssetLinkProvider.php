<?php

declare(strict_types=1);

namespace App\Lsp\Features\Assets;

use App\Lsp\Contracts\LinkProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class AssetLinkProvider implements LinkProvider
{
    /**
     * Create a new asset link provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {}

    /**
     * Provide asset links.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document): array
    {
        if (!$this->workspace->config->boolean('assetLink', true)) {
            return [];
        }

        return (new AssetDocumentMapper($this->workspace))->links($document);
    }
}
