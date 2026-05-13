<?php

declare(strict_types=1);

namespace App\Lsp\Features\Routes;

use App\Lsp\Contracts\DiagnosticProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class RouteDiagnosticProvider implements DiagnosticProvider
{
    /**
     * Create a new route diagnostic provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide route diagnostics for the given document.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document): array
    {
        if (!$this->workspace->config->boolean('routeDiagnostics', true)) {
            return [];
        }

        return (new RouteDocumentMapper($this->workspace))->diagnostics($document);
    }
}
