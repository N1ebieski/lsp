<?php

declare(strict_types=1);

namespace App\Lsp\Features\Assets;

use App\Lsp\Detection\AutocompleteArgument;
use App\Lsp\Detection\DetectedArgument;
use App\Lsp\Detection\Pattern;
use App\Lsp\Features\Support\DocumentMapper;
use App\Lsp\Support\Uri;
use Illuminate\Support\Collection;

class AssetDocumentMapper extends DocumentMapper
{
    /**
     * Create a new asset document mapper instance.
     *
     * @param  Collection<string, array<string, mixed>>  $assets
     */
    public function __construct(
        protected Collection $assets,
    ) {
        //
    }

    /**
     * Get asset detection patterns.
     *
     * @return array<int, Pattern>
     */
    protected function patterns(): array
    {
        return [
            Pattern::method(method: 'asset', argument: 0),
            Pattern::method(method: 'asset', class: Pattern::contract('Routing\\UrlGenerator'), argument: 0),
        ];
    }

    /**
     * Convert the given argument to document links.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toLinks(DetectedArgument $argument): array
    {
        $asset = $this->find($argument);

        if ($asset === null || ! is_string($asset['fullPath'] ?? null)) {
            return [];
        }

        return [[
            'range'  => $argument->range(),
            'target' => (string) Uri::fromPath($asset['fullPath']),
        ]];
    }

    /**
     * Convert the given argument to hover.
     *
     * @param  array<string, mixed>  $position
     * @return array<string, mixed>|null
     */
    protected function toHover(DetectedArgument $argument, array $position): ?array
    {
        return null;
    }

    /**
     * Convert the given argument to diagnostics.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toDiagnostics(DetectedArgument $argument): array
    {
        $value = $argument->stringValue();

        if ($value === null || $this->assets->has($value)) {
            return [];
        }

        return [[
            'range'    => $argument->range(),
            'severity' => 2,
            'source'   => 'Laravel Extension',
            'code'     => 'asset',
            'message'  => "Asset [{$value}] not found.",
        ]];
    }

    /**
     * Convert the given argument to completion items.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toCompletions(AutocompleteArgument $argument): array
    {
        return $this->assets
            ->keys()
            ->filter(fn (mixed $asset): bool => is_string($asset) && $asset !== '')
            ->map(fn (string $asset): array => [
                'label'    => $asset,
                'kind'     => 21,
                'textEdit' => [
                    'range'   => $argument->replacementRange(),
                    'newText' => $asset,
                ],
            ])
            ->values()
            ->all();
    }

    /**
     * Find the asset for the given argument.
     *
     * @return array<string, mixed>|null
     */
    protected function find(DetectedArgument $argument): ?array
    {
        $value = $argument->stringValue();

        if ($value === null) {
            return null;
        }

        $asset = $this->assets->get($value);

        return is_array($asset) ? $asset : null;
    }
}
