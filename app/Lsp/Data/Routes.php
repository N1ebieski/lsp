<?php

declare(strict_types=1);

namespace App\Lsp\Data;

use Illuminate\Support\Collection;

class Routes extends DataProvider
{
    /**
     * Get the routes template to run.
     */
    public function template(): string
    {
        return file_get_contents(__DIR__.'/Templates/routes.php');
    }

    /**
     * Parse the raw routes data.
     *
     * @param  array<int, array<string, mixed>>  $data
     */
    public function parse(array $data): Collection
    {
        return collect($data);
    }

    /**
     * Get routes keyed by controller action.
     *
     * @return Collection<string, array<string, mixed>>
     */
    public function controllerActions(): Collection
    {
        return $this->get()->keyBy('action');
    }

    /**
     * Get route-related watcher patterns.
     *
     * @return array<int, string>
     */
    public function patterns(): array
    {
        return [
            '**/{[Rr]oute}{,s}{.php,/*.php,/**/*.php}',
        ];
    }

    /**
     * Get the default route data.
     */
    protected function default(): Collection
    {
        return collect();
    }
}
