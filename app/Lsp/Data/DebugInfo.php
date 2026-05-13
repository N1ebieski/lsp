<?php

declare(strict_types=1);

namespace App\Lsp\Data;

class DebugInfo extends DataProvider
{
    /**
     * Get the debug info template to run.
     */
    public function template(): string
    {
        return file_get_contents(__DIR__.'/Templates/debug-info.php') ?: '';
    }

    /**
     * Parse the raw debug info data.
     *
     * @param  array<string, string>  $data
     * @return array<string, string>
     */
    public function parse(array $data): array
    {
        return $data;
    }

    /**
     * Get debug info watcher patterns.
     *
     * @return array<int, string>
     */
    public function patterns(): array
    {
        return [];
    }

    /**
     * Get the default debug info data.
     *
     * @return array<string, string>
     */
    protected function default(): array
    {
        return [];
    }
}
