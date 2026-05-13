<?php

declare(strict_types=1);

namespace App\Lsp\Features\LivewireComponents;

use App\Lsp\Contracts\CompletionProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class LivewireComponentCompletionProvider implements CompletionProvider
{
    /**
     * Create a new Livewire component completion provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {}

    /**
     * Provide Livewire component completions for the given document and position.
     *
     * @param  array<string, mixed>  $position
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document, array $position): array
    {
        if (!$this->workspace->config->boolean('livewireComponentCompletion', true)) {
            return [];
        }

        if (!str_ends_with($document->uri, '.blade.php')) {
            return [];
        }

        return (new LivewireComponentDocumentMapper($this->workspace))->completions($document, $position);
    }
}
