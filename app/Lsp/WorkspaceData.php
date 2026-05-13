<?php

declare(strict_types=1);

namespace App\Lsp;

use App\Lsp\Data\AppBindings;
use App\Lsp\Data\Assets;
use App\Lsp\Data\Auth;
use App\Lsp\Data\BladeComponents;
use App\Lsp\Data\Configs;
use App\Lsp\Data\Controllers;
use App\Lsp\Data\CustomBladeDirectives;
use App\Lsp\Data\DataProvider;
use App\Lsp\Data\DebugInfo;
use App\Lsp\Data\Env;
use App\Lsp\Data\InertiaViews;
use App\Lsp\Data\Middleware;
use App\Lsp\Data\MixManifest;
use App\Lsp\Data\Models;
use App\Lsp\Data\Paths;
use App\Lsp\Data\Routes;
use App\Lsp\Data\Tests;
use App\Lsp\Data\Translations;
use App\Lsp\Data\Views;

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
            'customBladeDirectives' => new CustomBladeDirectives($workspace->php),
            'debugInfo'   => new DebugInfo($workspace->php),
            'env'         => new Env($workspace),
            'inertiaViews' => new InertiaViews($workspace),
            'middleware'  => new Middleware($workspace->php),
            'mixManifest' => new MixManifest($workspace),
            'models'      => new Models($workspace->php),
            'paths'       => new Paths($workspace->php),
            'routes'      => new Routes($workspace->php),
            'tests'       => new Tests($workspace->php),
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
     * Get the custom Blade directives provider.
     */
    public function customBladeDirectives(): CustomBladeDirectives
    {
        return $this->providers['customBladeDirectives'];
    }

    /**
     * Get the debug info provider.
     */
    public function debugInfo(): DebugInfo
    {
        return $this->providers['debugInfo'];
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
     * Get the models provider.
     */
    public function models(): Models
    {
        return $this->providers['models'];
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
     * Get the tests provider.
     */
    public function tests(): Tests
    {
        return $this->providers['tests'];
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
     * Get a data provider by name.
     */
    public function get(string $name): ?DataProvider
    {
        return $this->providers[$name] ?? null;
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
}
