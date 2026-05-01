<?php

declare(strict_types=1);

namespace App\Lsp\Features\Env;

use App\Lsp\Detection\AutocompleteArgument;
use App\Lsp\Detection\DetectedArgument;
use App\Lsp\Detection\Pattern;
use App\Lsp\Features\Support\DocumentMapper;
use App\Lsp\Workspace;
use Illuminate\Support\Collection;

class EnvDocumentMapper extends DocumentMapper
{
    /**
     * Create a new env document mapper instance.
     *
     * @param  Collection<string, array<string, mixed>>  $env
     */
    public function __construct(
        protected Workspace $workspace,
        protected Collection $env,
    ) {
        //
    }

    /**
     * Get env detection patterns.
     *
     * @return array<int, Pattern>
     */
    protected function patterns(): array
    {
        return [
            Pattern::method(method: 'get', class: Pattern::support('Env'), argument: 0),
            Pattern::method(method: 'env', argument: 0),
        ];
    }

    /**
     * Convert the given argument to document links.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toLinks(DetectedArgument $argument): array
    {
        $item = $this->find($argument);

        return $item === null
            ? []
            : [$this->workspace->link($argument->range(), '.env', (int) $item['lineNumber'])];
    }

    /**
     * Convert the given argument to hover.
     *
     * @param  array<string, mixed>  $position
     * @return array<string, mixed>|null
     */
    protected function toHover(DetectedArgument $argument, array $position): ?array
    {
        $item = $this->find($argument);

        if ($item === null) {
            return null;
        }

        $value = $item['value'] === '' ? '[empty string]' : (string) $item['value'];

        return [
            'range'    => $argument->range(),
            'contents' => [
                'kind'  => 'markdown',
                'value' => "`{$value}`",
            ],
        ];
    }

    /**
     * Convert the given argument to diagnostics.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toDiagnostics(DetectedArgument $argument): array
    {
        $value = $argument->stringValue();

        if ($value === null || $this->env->has($value)) {
            return [];
        }

        return [[
            'range'    => $argument->range(),
            'severity' => 2,
            'source'   => 'Laravel Extension',
            'code'     => 'env',
            'message'  => "Env [{$value}] not found.",
        ]];
    }

    /**
     * Convert the given argument to completion items.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toCompletions(AutocompleteArgument $argument): array
    {
        return $this->env
            ->filter(fn (array $item, mixed $key): bool => is_string($key) && $key !== '')
            ->map(fn (array $item, string $key): array => [
                'label'    => $key,
                'kind'     => 21,
                'detail'   => (string) ($item['value'] ?? ''),
                'textEdit' => [
                    'range'   => $argument->replacementRange(),
                    'newText' => $key,
                ],
            ])
            ->values()
            ->all();
    }

    /**
     * Find the env value for the given argument.
     *
     * @return array<string, mixed>|null
     */
    protected function find(DetectedArgument $argument): ?array
    {
        $item = $this->env->get($argument->stringValue());

        return is_array($item) ? $item : null;
    }
}
