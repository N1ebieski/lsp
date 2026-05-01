<?php

declare(strict_types=1);

namespace App\Lsp\Features\Translations;

use App\Lsp\Contracts\CompletionProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class TranslationCompletionProvider implements CompletionProvider
{
    /**
     * Create a new translation completion provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide translation key completions for the given document and position.
     *
     * @param  array<string, mixed>  $position
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document, array $position): array
    {
        if (! $this->workspace->config->boolean('translationCompletion', true)) {
            return [];
        }

        return (new TranslationDocumentMapper(
            $this->workspace,
            $this->workspace->data->translations(),
        ))->completions($document, $position);
    }
}
