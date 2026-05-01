<?php

declare(strict_types=1);

namespace App\Lsp\Data;

use Illuminate\Support\Collection;

class Auth extends DataProvider
{
    /**
     * Get the auth template to run.
     */
    public function template(): string
    {
        return file_get_contents(__DIR__ . '/Templates/auth.php') ?: '';
    }

    /**
     * Parse the raw auth data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function parse(array $data): array
    {
        return $data;
    }

    /**
     * Get auth-related watcher patterns.
     *
     * @return array<int, string>
     */
    public function patterns(): array
    {
        return [
            'app/Providers/{,*,**/*}.php',
            'app/Models/{,*,**/*}.php',
            'app/Policies/{,*,**/*}.php',
        ];
    }

    /**
     * Get policies keyed by ability.
     *
     * @return Collection<string, Collection<int, array<string, mixed>>>
     */
    public function policies(): Collection
    {
        return collect($this->get()['policies'] ?? [])
            ->map(fn (array $items): Collection => collect($items));
    }

    /**
     * Get default auth data.
     *
     * @return array<string, mixed>
     */
    protected function default(): array
    {
        return [
            'authenticatable' => null,
            'policies'        => [],
        ];
    }
}
