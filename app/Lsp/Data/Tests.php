<?php

declare(strict_types=1);

namespace App\Lsp\Data;

class Tests extends DataProvider
{
    /**
     * Get the tests template to run.
     */
    public function template(): string
    {
        return file_get_contents(__DIR__ . '/Templates/tests.php') ?: '';
    }

    /**
     * Parse the raw tests data.
     *
     * @param  array<int, array<string, mixed>>  $data
     * @return array<int, array<string, mixed>>
     */
    public function parse(array $data): array
    {
        return $data;
    }

    /**
     * Get test-related watcher patterns.
     *
     * @return array<int, string>
     */
    public function patterns(): array
    {
        return [
            'tests/**/*',
            'phpunit.xml',
            'phpunit.xml.dist',
        ];
    }
}
