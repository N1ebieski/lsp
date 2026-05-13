<?php

declare(strict_types=1);

namespace App\Lsp\Data;

use Illuminate\Support\Collection;

class Views extends DataProvider
{
    /**
     * Get the views template to run.
     */
    public function template(): string
    {
        return file_get_contents(__DIR__ . '/Templates/views.php') ?: '';
    }

    /**
     * Parse the raw views data.
     *
     * @param  array<int, array<string, mixed>>  $data
     */
    public function parse(array $data): Collection
    {
        return collect($data);
    }

    /**
     * Get view-related watcher patterns.
     *
     * @return array<int, string>
     */
    public function patterns(): array
    {
        return [
            '**/{resources,Modules/*/resources}/views/**/*.blade.php',
        ];
    }

    /**
     * Get the default views data.
     */
    protected function default(): Collection
    {
        return collect();
    }
}
