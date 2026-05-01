<?php

declare(strict_types=1);

namespace App\Lsp\Features\Translations;

use App\Lsp\Contracts\HoverProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class TranslationHoverProvider implements HoverProvider
{
    /**
     * Create a new translation hover provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide translation hover for the given document and position.
     *
     * @param  array<string, mixed>  $position
     * @return array<string, mixed>|null
     */
    public function get(Document $document, array $position): ?array
    {
        if (! $this->workspace->config->boolean('translationHover', true)) {
            return null;
        }

        return (new TranslationDocumentMapper(
            $this->workspace,
            $this->workspace->data->translations(),
        ))->hover($document, $position);
    }
}
