<?php

declare(strict_types=1);

namespace App\Lsp\Data;

class Models extends DataProvider
{
    /**
     * Get the models template to run.
     */
    public function template(): string
    {
        return file_get_contents(__DIR__ . '/Templates/models.php') ?: '';
    }

    /**
     * Parse the raw model data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, array<string, mixed>>
     */
    public function parse(array $data): array
    {
        $models = $data['models'] ?? [];

        return is_array($models) ? $models : [];
    }

    /**
     * Get model-related watcher patterns.
     *
     * @return array<int, string>
     */
    public function patterns(): array
    {
        return [
            'app/{,*,**/*}.php',
            'database/migrations/{,*,**/*}.php',
            'composer.json',
            'composer.lock',
        ];
    }

    /**
     * Get the default model data.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function default(): array
    {
        return [];
    }
}
