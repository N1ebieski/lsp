<?php

declare(strict_types=1);

namespace App\Lsp\Features\Auth;

use App\Lsp\Contracts\LinkProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class AuthLinkProvider implements LinkProvider
{
    /**
     * Create a new auth link provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide auth document links for the given document.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document): array
    {
        if (!$this->workspace->config->boolean('authLink', true)) {
            return [];
        }

        return (new AuthDocumentMapper(
            $this->workspace,
            $this->workspace->data->auth()->policies(),
        ))->links($document);
    }
}
