<?php

declare(strict_types=1);

namespace App\Lsp\Data;

use App\Lsp\Workspace;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;

class Assets extends DataProvider
{
    /**
     * Create a new assets provider instance.
     */
    public function __construct(protected Workspace $workspace)
    {
        parent::__construct($workspace->php);
    }

    /**
     * Get the assets template to run.
     */
    public function template(): string
    {
        return '';
    }

    /**
     * Parse raw asset data.
     */
    public function parse(array $data): Collection
    {
        return collect($data);
    }

    /**
     * Get asset-related watcher patterns.
     *
     * @return array<int, string>
     */
    public function patterns(): array
    {
        return [
            'public/**/*',
        ];
    }

    /**
     * Get discovered public assets.
     */
    public function get(): Collection
    {
        if ($this->loaded) {
            return $this->data;
        }

        $public = $this->workspace->path('public');

        $this->loaded = true;

        if (! is_dir($public)) {
            return $this->data = collect();
        }

        return $this->data = collect(Finder::create()->files()->in($public)->depth('<= 10'))
            ->reject(fn (\Symfony\Component\Finder\SplFileInfo $file): bool => $file->getExtension() === 'php')
            ->map(fn (\Symfony\Component\Finder\SplFileInfo $file): array => [
                'path' => str_replace('\\', '/', ltrim(str_replace($public, '', $file->getRealPath() ?: $file->getPathname()), DIRECTORY_SEPARATOR)),
                'fullPath' => $file->getRealPath() ?: $file->getPathname(),
            ])
            ->values();
    }
}
