<?php

declare(strict_types=1);

namespace App\Lsp\Features\LivewireComponents;

use App\Lsp\Document;
use App\Lsp\Support\Position;
use App\Lsp\Workspace;

class LivewireComponentDocumentMapper
{
    /**
     * Create a new Livewire component document mapper instance.
     */
    public function __construct(
        protected Workspace $workspace,
    ) {
        //
    }

    /**
     * Get Livewire component document links.
     *
     * @return array<int, array<string, mixed>>
     */
    public function links(Document $document): array
    {
        return collect($this->matches($document))
            ->map(function (array $match): ?array {
                $view = $this->workspace->data->views()->livewireComponent($match['name']);

                return is_array($view) && is_string($view['path'] ?? null)
                    ? $this->workspace->link($match['range'], $view['path'])
                    : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Get Livewire component hover for the given position.
     *
     * @param  array<string, mixed>  $position
     * @return array<string, mixed>|null
     */
    public function hover(Document $document, array $position): ?array
    {
        foreach ($this->matches($document) as $match) {
            if (! Position::inRange($match['range'], $position)) {
                continue;
            }

            $view = $this->workspace->data->views()->livewireComponent($match['name']);
            $livewire = is_array($view) ? ($view['livewire'] ?? null) : null;

            if (! is_array($livewire)) {
                continue;
            }

            $lines = collect($livewire['files'] ?? [])
                ->filter(fn (mixed $path): bool => is_string($path))
                ->map(fn (string $path): string => "[{$path}]({$this->workspace->target($path)})")
                ->all();

            $props = collect($livewire['props'] ?? [])
                ->filter(fn (mixed $prop): bool => is_array($prop))
                ->map(fn (array $prop): string => ($prop['type'] ?? 'mixed').' $'.($prop['name'] ?? '').(($prop['hasDefaultValue'] ?? false) ? ' = '.($prop['defaultValue'] ?? '') : '').';')
                ->implode("\n");

            if ($props !== '') {
                $lines[] = "```php\n<?php\n{$props}\n```";
            }

            if ($lines === []) {
                return null;
            }

            return [
                'range'    => $match['range'],
                'contents' => [
                    'kind'  => 'markdown',
                    'value' => implode("\n\n", array_values(array_filter($lines))),
                ],
            ];
        }

        return null;
    }

    /**
     * Find Livewire tag matches.
     *
     * @return array<int, array{name: string, range: array<string, array<string, int>>}>
     */
    protected function matches(Document $document): array
    {
        $matches = [];

        foreach (explode("\n", $document->content) as $lineNumber => $line) {
            if (preg_match('/<\/?livewire:([^\s>]+)/', $line, $match, PREG_OFFSET_CAPTURE) !== 1) {
                continue;
            }

            $matches[] = [
                'name' => $match[1][0],
                'range' => [
                    'start' => ['line' => $lineNumber, 'character' => $match[0][1] + 1],
                    'end' => ['line' => $lineNumber, 'character' => $match[0][1] + strlen($match[0][0])],
                ],
            ];
        }

        return $matches;
    }
}
