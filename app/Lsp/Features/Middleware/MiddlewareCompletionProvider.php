<?php

declare(strict_types=1);

namespace App\Lsp\Features\Middleware;

use App\Lsp\Contracts\CompletionProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class MiddlewareCompletionProvider implements CompletionProvider
{
    /**
     * Create a new middleware completion provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {}

    /**
     * Provide middleware completions for the given document and position.
     *
     * @param  array<string, mixed>  $position
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document, array $position): array
    {
        if (!$this->workspace->config->boolean('middlewareCompletion', true)) {
            return [];
        }

        return (new MiddlewareDocumentMapper($this->workspace))->completions($document, $position);
    }
}
