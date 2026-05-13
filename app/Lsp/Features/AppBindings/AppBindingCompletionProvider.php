<?php

declare(strict_types=1);

namespace App\Lsp\Features\AppBindings;

use App\Lsp\Contracts\CompletionProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class AppBindingCompletionProvider implements CompletionProvider
{
    /**
     * Create a new app binding completion provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {}

    /**
     * Provide app binding completions for the given document and position.
     *
     * @param  array<string, mixed>  $position
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document, array $position): array
    {
        if (!$this->workspace->config->boolean('appBindingCompletion', true)) {
            return [];
        }

        return (new AppBindingDocumentMapper($this->workspace))->completions($document, $position);
    }
}
