<?php

declare(strict_types=1);

namespace App\Lsp\Data;

use Illuminate\Support\Collection;

class Middleware extends DataProvider
{
    /**
     * Get the middleware template to run.
     */
    public function template(): string
    {
        return file_get_contents(__DIR__.'/Templates/middleware.php') ?: '';
    }

    /**
     * Parse the raw middleware data.
     *
     * @param  array<string, array<string, mixed>>  $data
     */
    public function parse(array $data): Collection
    {
        return collect($data);
    }

    /**
     * Get middleware-related watcher patterns.
     *
     * @return array<int, string>
     */
    public function patterns(): array
    {
        return [
            'app/Http/Kernel.php',
            'bootstrap/app.php',
        ];
    }

    /**
     * Get the default middleware data.
     */
    protected function default(): Collection
    {
        return collect();
    }
}
