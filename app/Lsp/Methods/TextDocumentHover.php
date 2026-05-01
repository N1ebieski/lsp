<?php

declare(strict_types=1);

namespace App\Lsp\Methods;

use App\Lsp\Contracts\Method;
use App\Lsp\Transport\JsonRpcRequest;
use App\Lsp\Transport\JsonRpcResponse;
use App\Lsp\Workspace;

class TextDocumentHover implements Method
{
    /**
     * Handle the textDocument/hover request.
     */
    public function handle(JsonRpcRequest $request, Workspace $workspace): JsonRpcResponse
    {
        $document = $workspace->documents->get(
            (string) $request->get('textDocument.uri')
        );

        if ($document === null) {
            return JsonRpcResponse::result($request->id(), null);
        }

        $position = $request->get('position', []);

        if (!is_array($position)) {
            return JsonRpcResponse::result($request->id(), null);
        }

        foreach ($workspace->features->hovers() as $provider) {
            $hover = $provider->get($document, $position);

            if ($hover !== null) {
                return JsonRpcResponse::result($request->id(), $hover);
            }
        }

        return JsonRpcResponse::result($request->id(), null);
    }
}
