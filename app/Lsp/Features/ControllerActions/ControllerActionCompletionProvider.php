<?php

declare(strict_types=1);

namespace App\Lsp\Features\ControllerActions;

use App\Lsp\Contracts\CompletionProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class ControllerActionCompletionProvider implements CompletionProvider
{
    /**
     * Create a new controller action completion provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {}

    /**
     * Provide controller action completions for the given document and position.
     *
     * @param  array<string, mixed>  $position
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document, array $position): array
    {
        if (!$this->workspace->config->boolean('controllerActionCompletion', true)) {
            return [];
        }

        return (new ControllerActionDocumentMapper($this->workspace))->completions($document, $position);
    }
}
