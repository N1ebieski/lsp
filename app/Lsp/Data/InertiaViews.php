<?php

declare(strict_types=1);

namespace App\Lsp\Data;

use App\Lsp\Workspace;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;

class InertiaViews extends DataProvider
{
    /**
     * Create a new inertia views provider instance.
     */
    public function __construct(protected Workspace $workspace)
    {
        parent::__construct($workspace->php);
    }

    /**
     * Get the inertia template to run.
     */
    public function template(): string
    {
        return file_get_contents(__DIR__.'/Templates/inertia.php') ?: '';
    }

    /**
     * Parse the raw inertia config data.
     *
     * @param  array<string, mixed>  $data
     */
    public function parse(array $data): Collection
    {
        $paths = collect($data['page_paths'] ?? [])
            ->filter(fn (mixed $path): bool => is_string($path) && $path !== '')
            ->values();

        if ($paths->isEmpty()) {
            $paths = collect(['resources/js/Pages']);
        }

        $extensions = collect($data['page_extensions'] ?? [])
            ->filter(fn (mixed $extension): bool => is_string($extension) && $extension !== '')
            ->map(fn (string $extension): string => ltrim($extension, '.'))
            ->values();

        if ($extensions->isEmpty()) {
            $extensions = collect(['vue']);
        }

        return $paths
            ->flatMap(fn (string $path): Collection => $this->views($path, $extensions))
            ->keyBy('name');
    }

    /**
     * Get inertia-related watcher patterns.
     *
     * @return array<int, string>
     */
    public function patterns(): array
    {
        return [
            'resources/js/Pages/{*,**/*}',
            'resources/js/pages/{*,**/*}',
            'config/{,*,**/*}.php',
        ];
    }

    /**
     * Get the default inertia view data.
     */
    protected function default(): Collection
    {
        return collect();
    }

    /**
     * Discover Inertia views under a page path.
     *
     * @param  Collection<int, string>  $extensions
     * @return Collection<int, array<string, string>>
     */
    protected function views(string $path, Collection $extensions): Collection
    {
        $absolute = $this->workspace->path($path);

        if (! is_dir($absolute)) {
            return collect();
        }

        return collect(Finder::create()->files()->in($absolute))
            ->filter(fn (\Symfony\Component\Finder\SplFileInfo $file): bool => $extensions->contains($file->getExtension()))
            ->map(function (\Symfony\Component\Finder\SplFileInfo $file) use ($absolute, $path): array {
                $relative = ltrim(str_replace($absolute, '', $file->getRealPath() ?: $file->getPathname()), DIRECTORY_SEPARATOR);
                $name = preg_replace('/\.[^.]+$/', '', $relative) ?: $relative;

                return [
                    'name' => str_replace('\\', '/', $name),
                    'path' => trim($path, '/').'/'.str_replace('\\', '/', $relative),
                ];
            })
            ->values();
    }
}
