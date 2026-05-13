<?php

declare(strict_types=1);

namespace App\Lsp\Watchers;

use App\Lsp\Contracts\FileWatcher;
use App\Lsp\Support\Pattern;
use App\Lsp\Workspace;

class DataProviderWatcher implements FileWatcher
{
    /**
     * Create a new data provider watcher instance.
     */
    public function __construct(protected Workspace $workspace)
    {
        //
    }

    /**
     * Initialize the data provider watcher.
     */
    public function initialize(): void
    {
        //
    }

    /**
     * Get data provider watcher patterns.
     *
     * @return array<int, string>
     */
    public function patterns(): array
    {
        $patterns = [];

        foreach ($this->workspace->data->all() as $provider) {
            array_push($patterns, ...$provider->patterns());
        }

        return array_values(array_unique($patterns));
    }

    /**
     * Handle changed workspace-relative paths.
     *
     * @param  array<int, string>  $changes
     */
    public function onFileChange(array $changes): void
    {
        foreach ($this->workspace->data->all() as $provider) {
            if (Pattern::matchesAnyPath($changes, $provider->patterns())) {
                $provider->invalidate();
            }
        }
    }
}
