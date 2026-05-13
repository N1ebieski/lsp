<?php

declare(strict_types=1);

namespace App\Lsp\Features\Paths;

use App\Lsp\Contracts\LinkProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class PathLinkProvider implements LinkProvider
{
    /**
     * Create a new path link provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide Laravel path helper links.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document): array
    {
        if (!$this->workspace->config->boolean('pathsLink', true)) {
            return [];
        }

        return (new PathDocumentMapper($this->workspace))->links($document);
    }
}
