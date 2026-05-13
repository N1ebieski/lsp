<?php

declare(strict_types=1);

namespace App\Lsp\Features\Inertia;

use App\Lsp\Contracts\CompletionProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class InertiaCompletionProvider implements CompletionProvider
{
    /**
     * Create a new Inertia completion provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {}

    /**
     * Provide Inertia page completions for the given document and position.
     *
     * @param  array<string, mixed>  $position
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document, array $position): array
    {
        if (!$this->workspace->config->boolean('inertiaCompletion', true)) {
            return [];
        }

        return (new InertiaDocumentMapper($this->workspace))->completions($document, $position);
    }
}
