<?php

declare(strict_types=1);

namespace App\Lsp\Listeners;

use App\Lsp\Contracts\Listener;
use App\Lsp\Transport\JsonRpcRequest;
use App\Lsp\Workspace;

class OpenDocument implements Listener
{
    /**
     * Handle the textDocument/didOpen notification.
     */
    public function handle(JsonRpcRequest $request, Workspace $workspace): void
    {
        $workspace->documents->open(
            $request->get('textDocument.uri'),
            $request->get('textDocument.text'),
        );
    }
}
