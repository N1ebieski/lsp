<?php

declare(strict_types=1);

namespace App\Lsp\Listeners;

use App\Lsp\Contracts\Listener;
use App\Lsp\Transport\JsonRpcRequest;
use App\Lsp\Workspace;

class ClearDocumentDiagnostics implements Listener
{
    /**
     * Handle the incoming LSP notification.
     */
    public function handle(JsonRpcRequest $request, Workspace $workspace): void
    {
        $workspace->server->sendNotification('textDocument/publishDiagnostics', [
            'uri'         => $request->get('textDocument.uri'),
            'diagnostics' => [],
        ]);
    }
}
