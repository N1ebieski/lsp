<?php

declare(strict_types=1);

namespace App\Lsp\Methods;

use App\Lsp\Contracts\Method;
use App\Lsp\Document;
use App\Lsp\Support\Position;
use App\Lsp\Transport\JsonRpcRequest;
use App\Lsp\Transport\JsonRpcResponse;
use App\Lsp\Workspace;

class TextDocumentDefinition implements Method
{
    /**
     * Handle the textDocument/definition request.
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

        $locationLinks = [];

        foreach ($this->links($workspace, $document) as $link) {
            $range = $link['range'] ?? null;

            if (!is_array($range) || !Position::inRange($range, $position)) {
                continue;
            }

            $locationLink = $this->locationLink($link, $range);

            if ($locationLink !== null) {
                $locationLinks[] = $locationLink;
            }
        }

        return JsonRpcResponse::result($request->id(), $locationLinks);
    }

    /**
     * Get document links from every registered link provider.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function links(Workspace $workspace, Document $document): array
    {
        $links = [];

        foreach ($workspace->features->links() as $provider) {
            array_push($links, ...$provider->get($document));
        }

        return $links;
    }

    /**
     * Convert a document link into a definition LocationLink.
     *
     * @param  array<string, mixed>  $link
     * @param  array<string, mixed>  $originRange
     * @return array<string, mixed>|null
     */
    protected function locationLink(array $link, array $originRange): ?array
    {
        $target = $link['target'] ?? null;

        if (!is_string($target)) {
            return null;
        }

        [$targetUri, $targetRange] = $this->target($target);

        return [
            'originSelectionRange' => $originRange,
            'targetUri'            => $targetUri,
            'targetRange'          => $targetRange,
            'targetSelectionRange' => $targetRange,
        ];
    }

    /**
     * Get the target URI and range for a document link target.
     *
     * @return array{0: string, 1: array<string, array<string, int>>}
     */
    protected function target(string $target): array
    {
        $targetUri = $target;
        $line = 0;

        if (preg_match('/^(.*)#L([1-9][0-9]*)$/', $target, $matches) === 1) {
            $targetUri = $matches[1];
            $line = ((int) $matches[2]) - 1;
        }

        return [
            $targetUri,
            [
                'start' => [
                    'line'      => $line,
                    'character' => 0,
                ],
                'end' => [
                    'line'      => $line,
                    'character' => 0,
                ],
            ],
        ];
    }
}
