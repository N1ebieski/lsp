<?php

declare(strict_types=1);

namespace App\Lsp\Data;

class Translations extends DataProvider
{
    /**
     * Get the translations template to run.
     */
    public function template(): string
    {
        return file_get_contents(__DIR__ . '/Templates/translations.php') ?: '';
    }

    /**
     * Parse the raw translation data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function parse(array $data): array
    {
        $paths = is_array($data['paths'] ?? null) ? $data['paths'] : [];
        $values = is_array($data['values'] ?? null) ? $data['values'] : [];
        $params = is_array($data['params'] ?? null) ? $data['params'] : [];
        $translations = [];

        foreach (($data['translations'] ?? []) as $key => $locales) {
            if (!is_array($locales)) {
                continue;
            }

            foreach ($locales as $locale => $definition) {
                if (!is_array($definition)) {
                    continue;
                }

                [$value, $path, $line, $param] = array_pad($definition, 4, null);

                $translations[$key][$locale] = [
                    'value'  => is_int($value) && isset($values[$value]) ? $values[$value] : '',
                    'path'   => is_int($path) && isset($paths[$path]) ? $paths[$path] : '',
                    'line'   => is_int($line) ? $line : null,
                    'params' => is_int($param) && isset($params[$param]) ? $params[$param] : [],
                ];
            }
        }

        return [
            'default'      => $data['default'] ?? '',
            'translations' => $translations,
            'languages'    => $data['languages'] ?? [],
            'paths'        => $paths,
        ];
    }

    /**
     * Get translation-related watcher patterns.
     *
     * @return array<int, string>
     */
    public function patterns(): array
    {
        return [
            'lang/{*,**/*}',
            'resources/lang/{*,**/*}',
        ];
    }

    /**
     * Get default translation data.
     *
     * @return array<string, mixed>
     */
    protected function default(): array
    {
        return [
            'default'      => '',
            'translations' => [],
            'languages'    => [],
            'paths'        => [],
        ];
    }
}
