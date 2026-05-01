<?php

declare(strict_types=1);

namespace App\Lsp\Features\Middleware;

use App\Lsp\Detection\AutocompleteArgument;
use App\Lsp\Detection\DetectedArgument;
use App\Lsp\Detection\DetectedArguments;
use App\Lsp\Detection\Pattern;
use App\Lsp\Document;
use App\Lsp\Features\Support\DocumentMapper;
use App\Lsp\Support\Position;
use App\Lsp\Workspace;
use Illuminate\Support\Collection;

class MiddlewareDocumentMapper extends DocumentMapper
{
    /**
     * Create a new middleware document mapper instance.
     *
     * @param  Collection<string, array<string, mixed>>  $middleware
     */
    public function __construct(
        protected Workspace $workspace,
        protected Collection $middleware,
    ) {
        //
    }

    /**
     * Get middleware detection patterns.
     *
     * @return array<int, Pattern>
     */
    protected function patterns(): array
    {
        return [
            Pattern::attribute(class: 'Illuminate\\Routing\\Attributes\\Controllers\\Middleware', argument: 0),
            Pattern::method(method: ['middleware', 'withoutMiddleware'], class: Pattern::facade('Route'), argument: [0, 1, 2]),
        ];
    }

    /**
     * Get matched middleware arguments from the document.
     *
     * @return Collection<int, DetectedArgument>
     */
    public function arguments(Document $document): Collection
    {
        return DetectedArguments::in($document)
            ->matching($this->patterns())
            ->stringsAndArrays()
            ->filter(fn (DetectedArgument $argument): bool => $this->shouldAccept($argument))
            ->values();
    }

    /**
     * Convert the given argument to document links.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toLinks(DetectedArgument $argument): array
    {
        return collect($argument->stringValues())
            ->map(function (array $value): ?array {
                $item = $this->middleware->get($this->name($value['value']));

                return is_array($item) && is_string($item['path'] ?? null)
                    ? $this->workspace->link($value['range'], $item['path'], is_numeric($item['line'] ?? null) ? (int) $item['line'] : null)
                    : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Convert the given argument to hover.
     *
     * @param  array<string, mixed>  $position
     * @return array<string, mixed>|null
     */
    protected function toHover(DetectedArgument $argument, array $position): ?array
    {
        foreach ($argument->stringValues() as $value) {
            if (! Position::inRange($value['range'], $position)) {
                continue;
            }

            $item = $this->middleware->get($this->name($value['value']));

            if (! is_array($item)) {
                continue;
            }

            if (is_string($item['path'] ?? null)) {
                return $this->markdownHover($value['range'], [
                    $this->markdownPath($item['path'], is_numeric($item['line'] ?? null) ? (int) $item['line'] : null),
                ]);
            }

            $groups = collect($item['groups'] ?? [])
                ->map(fn (array $group): string => is_string($group['path'] ?? null)
                    ? $this->markdownPath($group['path'], is_numeric($group['line'] ?? null) ? (int) $group['line'] : null)
                    : (string) ($group['class'] ?? ''))
                ->filter()
                ->values()
                ->all();

            if ($groups !== []) {
                return $this->markdownHover($value['range'], $groups);
            }
        }

        return null;
    }

    /**
     * Convert the given argument to diagnostics.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toDiagnostics(DetectedArgument $argument): array
    {
        return collect($argument->stringValues())
            ->reject(fn (array $value): bool => $this->middleware->has($this->name($value['value'])))
            ->map(fn (array $value): array => [
                'range'    => $value['range'],
                'severity' => 2,
                'source'   => 'Laravel Extension',
                'code'     => 'middleware',
                'message'  => "Middleware [{$value['value']}] not found.",
            ])
            ->values()
            ->all();
    }

    /**
     * Convert the given argument to completion items.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toCompletions(AutocompleteArgument $argument): array
    {
        return $this->middleware
            ->filter(fn (array $item, mixed $key): bool => is_string($key) && $key !== '')
            ->map(fn (array $item, string $key): array => [
                'label'    => $key,
                'kind'     => 13,
                'detail'   => (string) ($item['parameters'] ?? ''),
                'textEdit' => [
                    'range'   => $argument->replacementRange(),
                    'newText' => $key,
                ],
            ])
            ->values()
            ->all();
    }

    /**
     * Get the middleware name without parameters.
     */
    protected function name(string $value): string
    {
        return explode(':', $value)[0];
    }

    /**
     * Create a markdown hover response.
     *
     * @param  array<string, array<string, int>>  $range
     * @param  array<int, string>  $lines
     * @return array<string, mixed>
     */
    protected function markdownHover(array $range, array $lines): array
    {
        return [
            'range'    => $range,
            'contents' => [
                'kind'  => 'markdown',
                'value' => implode("\n\n", array_values(array_filter($lines))),
            ],
        ];
    }

    /**
     * Get a markdown link for a workspace path.
     */
    protected function markdownPath(string $path, ?int $line = null): string
    {
        return "[{$path}]({$this->workspace->target($path, $line)})";
    }
}
