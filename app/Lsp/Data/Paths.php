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
        return <<<'PHP'
echo json_encode([
    ['key' => 'base_path', 'path' => base_path()],
    ['key' => 'resource_path', 'path' => resource_path()],
    ['key' => 'config_path', 'path' => config_path()],
    ['key' => 'app_path', 'path' => app_path()],
    ['key' => 'database_path', 'path' => database_path()],
    ['key' => 'lang_path', 'path' => lang_path()],
    ['key' => 'public_path', 'path' => public_path()],
    ['key' => 'storage_path', 'path' => storage_path()],
]);
PHP;
    }

    /**
     * Parse the raw paths data.
     *
     * @param  array<int, array<string, string>>  $data
     */
    public function parse(array $data): Collection
    {
        return collect($data)->keyBy('key');
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
