<?php

declare(strict_types=1);

namespace App\Lsp\Methods;

use App\Lsp\Contracts\Method;
use App\Lsp\Transport\JsonRpcRequest;
use App\Lsp\Transport\JsonRpcResponse;
use App\Lsp\Workspace;

class TextDocumentCompletion implements Method
{
    /**
     * Handle the textDocument/completion request.
     */
    public function handle(JsonRpcRequest $request, Workspace $workspace): JsonRpcResponse
    {
        $document = $workspace->documents->get(
            (string) $request->get('textDocument.uri')
        );

        if ($document === null) {
            return JsonRpcResponse::result($request->id(), []);
        }

        $position = $request->get('position', []);

        if (!is_array($position)) {
            return JsonRpcResponse::result($request->id(), []);
        }

        if (!is_int($position['line'] ?? null) || !is_int($position['character'] ?? null)) {
            return JsonRpcResponse::result($request->id(), []);
        }

        foreach ($workspace->features->completions() as $provider) {
            $items = $provider->get($document, $position);

            if ($items !== []) {
                return JsonRpcResponse::result($request->id(), $items);
            }
        }

        return JsonRpcResponse::result($request->id(), []);
    }
}
