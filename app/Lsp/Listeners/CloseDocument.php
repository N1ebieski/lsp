<?php

declare(strict_types=1);

namespace App\Lsp\Listeners;

use App\Lsp\Contracts\Listener;
use App\Lsp\Transport\JsonRpcRequest;
use App\Lsp\Workspace;

class CloseDocument implements Listener
{
    /**
     * Handle the textDocument/didClose notification.
     */
    public function handle(JsonRpcRequest $request, Workspace $workspace): void
    {
        $workspace->documents->close(
            $request->get('textDocument.uri')
        );
    }
}
