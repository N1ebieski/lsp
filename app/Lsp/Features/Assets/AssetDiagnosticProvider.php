<?php

declare(strict_types=1);

namespace App\Lsp\Features\Assets;

use App\Lsp\Contracts\DiagnosticProvider;
use App\Lsp\Document;
use App\Lsp\Workspace;

class AssetDiagnosticProvider implements DiagnosticProvider
{
    /**
     * Create a new asset diagnostic provider instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Provide asset diagnostics.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(Document $document): array
    {
        if (! $this->workspace->config->boolean('assetDiagnostics', true)) {
            return [];
        }

        return (new AssetDocumentMapper(
            $this->workspace->data->assets()->get()->keyBy('path'),
        ))->diagnostics($document);
    }
}
