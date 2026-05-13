<?php

declare(strict_types=1);

namespace App\Lsp\Data;

use Illuminate\Support\Collection;

class Paths extends DataProvider
{
    /**
     * Get the paths template to run.
     */
    public function template(): string
    {
        return file_get_contents(__DIR__ . '/Templates/paths.php') ?: '';
    }

    /**
     * Parse the raw paths data.
     *
     * @param  array<int, array<string, string>>  $data
     */
    public function parse(array $data): Collection
    {
        return collect($data);
    }

    /**
     * Get path-related watcher patterns.
     *
     * @return array<int, string>
     */
    public function patterns(): array
    {
        return [
            'config/{,*,**/*}.php',
        ];
    }

    /**
     * Get the default paths data.
     */
    protected function default(): Collection
    {
        return collect();
    }
}
