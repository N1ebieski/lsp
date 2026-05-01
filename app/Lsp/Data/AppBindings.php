<?php

declare(strict_types=1);

namespace App\Lsp\Data;

use Illuminate\Support\Collection;

class AppBindings extends DataProvider
{
    /**
     * Get the app bindings template to run.
     */
    public function template(): string
    {
        return file_get_contents(__DIR__.'/Templates/app-bindings.php') ?: '';
    }

    /**
     * Parse the raw app bindings data.
     *
     * @param  array<string, array<string, mixed>>  $data
     */
    public function parse(array $data): Collection
    {
        return collect($data);
    }

    /**
     * Get app binding-related watcher patterns.
     *
     * @return array<int, string>
     */
    public function patterns(): array
    {
        return [
            'app/Providers/{,*,**/*}.php',
        ];
    }

    /**
     * Get the default app bindings data.
     */
    protected function default(): Collection
    {
        return collect();
    }
}
