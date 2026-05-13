<?php

declare(strict_types=1);

namespace App\Lsp\Data;

class BladeComponents extends DataProvider
{
    /**
     * Get the Blade components template to run.
     */
    public function template(): string
    {
        return file_get_contents(__DIR__ . '/Templates/blade-components.php') ?: '';
    }

    /**
     * Parse the raw Blade component data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function parse(array $data): array
    {
        return $data;
    }

    /**
     * Get Blade component-related watcher patterns.
     *
     * @return array<int, string>
     */
    public function patterns(): array
    {
        return [
            '**/{resources,Modules/*/resources}/views/**/*.blade.php',
            'app/View/Components/{,*,**/*}.php',
        ];
    }

    /**
     * Get the default Blade component data.
     *
     * @return array<string, mixed>
     */
    protected function default(): array
    {
        return [
            'components' => [],
            'prefixes'   => [],
        ];
    }
}
