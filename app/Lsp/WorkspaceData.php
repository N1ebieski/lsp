<?php

declare(strict_types=1);

namespace App\Lsp;

use App\Lsp\Data\AppBindings;
use App\Lsp\Data\Assets;
use App\Lsp\Data\Auth;
use App\Lsp\Data\BladeComponents;
use App\Lsp\Data\Configs;
use App\Lsp\Data\Controllers;
use App\Lsp\Data\DataProvider;
use App\Lsp\Data\Env;
use App\Lsp\Data\InertiaViews;
use App\Lsp\Data\Middleware;
use App\Lsp\Data\MixManifest;
use App\Lsp\Data\Paths;
use App\Lsp\Data\Routes;
use App\Lsp\Data\Translations;
use App\Lsp\Data\Views;
use App\Lsp\Support\Uri;

class WorkspaceData
{
    /**
     * The provider instances keyed by name.
     *
     * @var array<string, DataProvider>
     */
    protected array $providers = [];

    /**
     * Create a new workspace data instance.
     */
    public function __construct(protected Workspace $workspace)
    {
        $this->providers = [
            'appBindings' => new AppBindings($workspace->php),
            'assets'      => new Assets($workspace),
            'auth'        => new Auth($workspace->php),
            'bladeComponents' => new BladeComponents($workspace->php),
            'configs'     => new Configs($workspace->php),
            'controllers' => new Controllers($workspace),
            'env'         => new Env($workspace),
            'inertiaViews' => new InertiaViews($workspace),
            'middleware'  => new Middleware($workspace->php),
            'mixManifest' => new MixManifest($workspace),
            'paths'       => new Paths($workspace->php),
            'routes'      => new Routes($workspace->php),
            'translations' => new Translations($workspace->php),
            'views'       => new Views($workspace->php),
        ];
    }

    /**
     * Get the assets provider.
     */
    public function assets(): Assets
    {
        return $this->providers['assets'];
    }

    /**
     * Get the auth provider.
     */
    public function auth(): Auth
    {
        return $this->providers['auth'];
    }

    /**
     * Get the Blade components provider.
     */
    public function bladeComponents(): BladeComponents
    {
        return $this->providers['bladeComponents'];
    }

    /**
     * Get the configs provider.
     */
    public function configs(): Configs
    {
        return $this->providers['configs'];
    }

    /**
     * Get the controllers provider.
     */
    public function controllers(): Controllers
    {
        return $this->providers['controllers'];
    }

    /**
     * Get the env provider.
     */
    public function env(): Env
    {
        return $this->providers['env'];
    }

    /**
     * Get the inertia views provider.
     */
    public function inertiaViews(): InertiaViews
    {
        return $this->providers['inertiaViews'];
    }

    /**
     * Get the middleware provider.
     */
    public function middleware(): Middleware
    {
        return $this->providers['middleware'];
    }

    /**
     * Get the mix manifest provider.
     */
    public function mixManifest(): MixManifest
    {
        return $this->providers['mixManifest'];
    }

    /**
     * Get the paths provider.
     */
    public function paths(): Paths
    {
        return $this->providers['paths'];
    }

    /**
     * Get the app bindings provider.
     */
    public function appBindings(): AppBindings
    {
        return $this->providers['appBindings'];
    }

    /**
     * Get the routes provider.
     */
    public function routes(): Routes
    {
        return $this->providers['routes'];
    }

    /**
     * Get the translations provider.
     */
    public function translations(): Translations
    {
        return $this->providers['translations'];
    }

    /**
     * Get the views provider.
     */
    public function views(): Views
    {
        return $this->providers['views'];
    }

    /**
     * Invalidate providers after watched files change.
     *
     * @param  array<int, array<string, mixed>>  $changes
     */
    public function invalidate(array $changes): void
    {
        if ($changes === []) {
            return;
        }

        $paths = collect($changes)
            ->pluck('uri')
            ->filter(fn (mixed $uri): bool => is_string($uri))
            ->map($this->relativePathFromUri(...))
            ->unique()
            ->values()
            ->all();

        if ($paths === []) {
            return;
        }

        foreach ($this->providers as $provider) {
            foreach ($paths as $path) {
                if (! $provider->matches($path)) {
                    continue;
                }

                $provider->invalidate();

                break;
            }
        }
    }

    /**
     * Get all data providers.
     *
     * @return array<string, DataProvider>
     */
    public function all(): array
    {
        return $this->providers;
    }

    /**
     * Get a workspace-relative path from a file URI.
     */
    protected function relativePathFromUri(string $uri): ?string
    {
        if (parse_url($uri, PHP_URL_SCHEME) !== 'file') {
            return null;
        }

        $path = Uri::of($uri)->path();

        if ($path === '') {
            return null;
        }

        $relativePath = Uri::of($this->workspace->baseUri)->relativePath($path);

        return $relativePath === $path ? null : $relativePath;
    }
}
