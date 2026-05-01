<?php

declare(strict_types=1);

namespace App\Lsp\Listeners;

use App\Lsp\Contracts\Listener;
use App\Lsp\Transport\JsonRpcRequest;
use App\Lsp\Workspace;

class UpdateDocument implements Listener
{
    /**
     * Handle the textDocument/didChange notification.
     */
    public function handle(JsonRpcRequest $request, Workspace $workspace): void
    {
        $content = $request->collect('contentChanges');

        if ($content->isEmpty()) {
            return;
        }

        $workspace->documents->update(
            $request->get('textDocument.uri'),
            $content->last()['text'],
        );
    }
}
