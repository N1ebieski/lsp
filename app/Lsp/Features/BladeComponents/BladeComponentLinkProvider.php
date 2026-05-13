<?php

declare(strict_types=1);

namespace App\Lsp\Features\BladeComponents;

use App\Lsp\Contracts\LinkProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class BladeComponentLinkProvider implements LinkProvider
{
    /**
     * Create a new Blade component link provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {}

    /**
     * Provide Blade component document links for the given document.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document): array
    {
        if (!$this->workspace->config->boolean('bladeComponentLink', true)) {
            return [];
        }

        return (new BladeComponentDocumentMapper($this->workspace))->links($document);
    }
}
