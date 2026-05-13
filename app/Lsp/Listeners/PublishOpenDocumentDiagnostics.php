<?php

declare(strict_types=1);

namespace App\Lsp\Listeners;

use App\Lsp\Contracts\Listener;
use App\Lsp\Transport\JsonRpcRequest;
use App\Lsp\Workspace;

class PublishOpenDocumentDiagnostics implements Listener
{
    /**
     * Handle the workspace/didChangeWatchedFiles notification.
     */
    public function handle(JsonRpcRequest $request, Workspace $workspace): void
    {
        $publisher = new PublishDiagnostics;

        foreach ($workspace->documents->all() as $document) {
            $publisher->publish($document, $workspace);
        }
    }
}
