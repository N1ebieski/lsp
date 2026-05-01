<?php

declare(strict_types=1);

namespace App\Lsp\Features\Auth;

use App\Lsp\Contracts\DiagnosticProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class AuthDiagnosticProvider implements DiagnosticProvider
{
    /**
     * Create a new auth diagnostic provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide auth diagnostics for the given document.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document): array
    {
        if (!$this->workspace->config->boolean('authDiagnostics', true)) {
            return [];
        }

        return (new AuthDocumentMapper(
            $this->workspace,
            $this->workspace->data->auth()->policies(),
        ))->diagnostics($document);
    }
}
