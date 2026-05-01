<?php

declare(strict_types=1);

namespace App\Lsp\Methods;

use App\Lsp\Contracts\Method;
use App\Lsp\Transport\JsonRpcRequest;
use App\Lsp\Transport\JsonRpcResponse;
use App\Lsp\Workspace;

class TextDocumentDocumentLink implements Method
{
    /**
     * Handle the textDocument/documentLink request.
     */
    public function handle(JsonRpcRequest $request, Workspace $workspace): JsonRpcResponse
    {
        $document = $workspace->documents->get(
            (string) $request->get('textDocument.uri')
        );

        if (is_null($document)) {
            return JsonRpcResponse::result($request->id(), []);
        }

        $links = [];

        foreach ($workspace->features->links() as $provider) {
            array_push($links, ...$provider->get($document));
        }

        return JsonRpcResponse::result($request->id(), $links);
    }
}
