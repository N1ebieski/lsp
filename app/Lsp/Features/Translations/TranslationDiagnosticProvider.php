<?php

declare(strict_types=1);

namespace App\Lsp\Features\Translations;

use App\Lsp\Contracts\DiagnosticProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class TranslationDiagnosticProvider implements DiagnosticProvider
{
    /**
     * Create a new translation diagnostic provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide translation diagnostics for the given document.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document): array
    {
        if (! $this->workspace->config->boolean('translationDiagnostics', true)) {
            return [];
        }

        return (new TranslationDocumentMapper(
            $this->workspace,
            $this->workspace->data->translations(),
        ))->diagnostics($document);
    }
}
