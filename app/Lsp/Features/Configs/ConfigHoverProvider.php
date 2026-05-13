<?php

declare(strict_types=1);

namespace App\Lsp\Features\Configs;

use App\Lsp\Contracts\HoverProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class ConfigHoverProvider implements HoverProvider
{
    /**
     * Create a new config hover provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide config hover for the given document and position.
     *
     * @param  array<string, mixed>  $position
     * @return array<string, mixed>|null
     */
    public function get(Document $document, array $position): ?array
    {
        if (!$this->workspace->config->boolean('configHover', true)) {
            return null;
        }

        return (new ConfigDocumentMapper($this->workspace))->hover($document, $position);
    }
}
