<?php

declare(strict_types=1);

namespace App\Lsp\Features\Env;

use App\Lsp\Contracts\HoverProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class EnvHoverProvider implements HoverProvider
{
    /**
     * Create a new env hover provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide env hover for the given document and position.
     *
     * @param  array<string, mixed>  $position
     * @return array<string, mixed>|null
     */
    public function get(Document $document, array $position): ?array
    {
        if (! $this->workspace->config->boolean('envHover', true)) {
            return null;
        }

        return (new EnvDocumentMapper(
            $this->workspace,
            $this->workspace->data->env()->get(),
        ))->hover($document, $position);
    }
}
