<?php

declare(strict_types=1);

namespace App\Lsp\Methods;

use App\Lsp\Contracts\Method;
use App\Lsp\Transport\JsonRpcRequest;
use App\Lsp\Transport\JsonRpcResponse;
use App\Lsp\Workspace;
use Illuminate\Contracts\Support\Arrayable;

class LaravelData implements Method
{
    /**
     * Handle the laravel/data request.
     */
    public function handle(JsonRpcRequest $request, Workspace $workspace): JsonRpcResponse
    {
        if (!$name = $request->get('name')) {
            return JsonRpcResponse::error(
                $request->id(),
                -32602,
                'Invalid params: The [name] parameter is required.',
            );
        }

        if (!$provider = $workspace->data->get($name)) {
            return JsonRpcResponse::error(
                $request->id(),
                -32602,
                "Invalid params: The [{$name}] data provider was not found.",
            );
        }

        $data = $provider->get();

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        return JsonRpcResponse::result(
            $request->id(),
            (array) $data,
        );
    }
}
