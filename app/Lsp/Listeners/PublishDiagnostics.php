<?php

declare(strict_types=1);

namespace App\Lsp\Listeners;

use App\Lsp\Contracts\Listener;
use App\Lsp\Transport\JsonRpcRequest;
use App\Lsp\Workspace;

class PublishDiagnostics implements Listener
{
    /**
     * Handle the incoming LSP notification.
     */
    public function handle(JsonRpcRequest $request, Workspace $workspace): void
    {
        $document = $workspace->documents->get(
            $request->get('textDocument.uri')
        );

        if ($document === null) {
            return;
        }

        $diagnostics = [];

        foreach ($workspace->features->diagnostics() as $provider) {
            array_push($diagnostics, ...$provider->get($document));
        }

        $workspace->server->sendNotification('textDocument/publishDiagnostics', [
            'uri'         => $document->uri,
            'diagnostics' => $diagnostics,
        ]);
    }
}
