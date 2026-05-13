<?php

declare(strict_types=1);

namespace App\Lsp\Methods;

use App\Lsp\CodeActions\CodeActionContext;
use App\Lsp\Contracts\Method;
use App\Lsp\Transport\JsonRpcRequest;
use App\Lsp\Transport\JsonRpcResponse;
use App\Lsp\Workspace;

class TextDocumentCodeAction implements Method
{
    /**
     * Handle the textDocument/codeAction request.
     */
    public function handle(JsonRpcRequest $request, Workspace $workspace): JsonRpcResponse
    {
        $document = $workspace->documents->get(
            (string) $request->get('textDocument.uri')
        );

        if ($document === null) {
            return JsonRpcResponse::result($request->id(), []);
        }

        $range = $request->get('range', []);
        $context = $request->get('context', []);

        if (!is_array($range) || !is_array($context)) {
            return JsonRpcResponse::result($request->id(), []);
        }

        $actions = [];
        $codeActionContext = new CodeActionContext($range, $context);

        foreach ($workspace->features->codeActions() as $provider) {
            array_push($actions, ...$provider->get($document, $codeActionContext));
        }

        return JsonRpcResponse::result($request->id(), $actions);
    }
}
