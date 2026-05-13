<?php

declare(strict_types=1);

namespace App\Lsp\Features\Translations;

use App\Lsp\Contracts\LinkProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class TranslationLinkProvider implements LinkProvider
{
    /**
     * Create a new translation link provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide translation links for the given document.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document): array
    {
        if (!$this->workspace->config->boolean('translationLink', true)) {
            return [];
        }

        return (new TranslationDocumentMapper($this->workspace))->links($document);
    }
}
