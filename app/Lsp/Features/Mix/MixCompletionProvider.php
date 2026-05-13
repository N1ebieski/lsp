<?php

declare(strict_types=1);

namespace App\Lsp\Features\Mix;

use App\Lsp\Contracts\CompletionProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class MixCompletionProvider implements CompletionProvider
{
    /**
     * Create a new mix completion provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide mix completions for the given document and position.
     *
     * @param  array<string, mixed>  $position
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document, array $position): array
    {
        if (!$this->workspace->config->boolean('mixCompletion', true)) {
            return [];
        }

        return (new MixDocumentMapper($this->workspace))->completions($document, $position);
    }
}
