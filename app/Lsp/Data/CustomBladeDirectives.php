<?php

declare(strict_types=1);

namespace App\Lsp\Data;

class CustomBladeDirectives extends DataProvider
{
    /**
     * Get the custom Blade directives template to run.
     */
    public function template(): string
    {
        return file_get_contents(__DIR__.'/Templates/blade-directives.php') ?: '';
    }

    /**
     * Parse the raw custom Blade directive data.
     *
     * @param  array<int, array<string, mixed>>  $data
     * @return array<int, array{name: string, hasParams: bool}>
     */
    public function parse(array $data): array
    {
        return collect($data)
            ->filter(fn (mixed $directive): bool => is_array($directive) && is_string($directive['name'] ?? null))
            ->map(fn (array $directive): array => [
                'name'      => $directive['name'],
                'hasParams' => (bool) ($directive['hasParams'] ?? false),
            ])
            ->values()
            ->all();
    }

    /**
     * Get custom Blade directive-related watcher patterns.
     *
     * @return array<int, string>
     */
    public function patterns(): array
    {
        return [
            'app/{,*,**/*}Provider.php',
        ];
    }
}
