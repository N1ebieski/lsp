<?php

declare(strict_types=1);

namespace App\Lsp\Features\Env;

use App\Lsp\Contracts\DiagnosticProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class EnvDiagnosticProvider implements DiagnosticProvider
{
    /**
     * Create a new env diagnostic provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {}

    /**
     * Provide env diagnostics for the given document.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document): array
    {
        if (!$this->workspace->config->boolean('envDiagnostics', true)) {
            return [];
        }

        return (new EnvDocumentMapper($this->workspace))->diagnostics($document);
    }
}
