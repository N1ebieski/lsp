<?php

declare(strict_types=1);

namespace App\Lsp;

use App\Lsp\Contracts\CompletionProvider;
use App\Lsp\Contracts\DiagnosticProvider;
use App\Lsp\Contracts\HoverProvider;
use App\Lsp\Contracts\LinkProvider;
use App\Lsp\Features\AppBindings\AppBindingCompletionProvider;
use App\Lsp\Features\AppBindings\AppBindingDiagnosticProvider;
use App\Lsp\Features\AppBindings\AppBindingHoverProvider;
use App\Lsp\Features\AppBindings\AppBindingLinkProvider;
use App\Lsp\Features\Assets\AssetCompletionProvider;
use App\Lsp\Features\Assets\AssetDiagnosticProvider;
use App\Lsp\Features\Assets\AssetLinkProvider;
use App\Lsp\Features\Auth\AuthCompletionProvider;
use App\Lsp\Features\Auth\AuthDiagnosticProvider;
use App\Lsp\Features\Auth\AuthHoverProvider;
use App\Lsp\Features\Auth\AuthLinkProvider;
use App\Lsp\Features\BladeComponents\BladeComponentHoverProvider;
use App\Lsp\Features\BladeComponents\BladeComponentLinkProvider;
use App\Lsp\Features\Configs\ConfigCompletionProvider;
use App\Lsp\Features\Configs\ConfigDiagnosticProvider;
use App\Lsp\Features\Configs\ConfigHoverProvider;
use App\Lsp\Features\Configs\ConfigLinkProvider;
use App\Lsp\Features\ControllerActions\ControllerActionCompletionProvider;
use App\Lsp\Features\ControllerActions\ControllerActionDiagnosticProvider;
use App\Lsp\Features\ControllerActions\ControllerActionLinkProvider;
use App\Lsp\Features\Env\EnvCompletionProvider;
use App\Lsp\Features\Env\EnvDiagnosticProvider;
use App\Lsp\Features\Env\EnvHoverProvider;
use App\Lsp\Features\Env\EnvLinkProvider;
use App\Lsp\Features\Inertia\InertiaCompletionProvider;
use App\Lsp\Features\Inertia\InertiaDiagnosticProvider;
use App\Lsp\Features\Inertia\InertiaHoverProvider;
use App\Lsp\Features\Inertia\InertiaLinkProvider;
use App\Lsp\Features\Inertia\InertiaPropertyCompletionProvider;
use App\Lsp\Features\LivewireComponents\LivewireComponentHoverProvider;
use App\Lsp\Features\LivewireComponents\LivewireComponentLinkProvider;
use App\Lsp\Features\Middleware\MiddlewareCompletionProvider;
use App\Lsp\Features\Middleware\MiddlewareDiagnosticProvider;
use App\Lsp\Features\Middleware\MiddlewareHoverProvider;
use App\Lsp\Features\Middleware\MiddlewareLinkProvider;
use App\Lsp\Features\Mix\MixCompletionProvider;
use App\Lsp\Features\Mix\MixDiagnosticProvider;
use App\Lsp\Features\Mix\MixHoverProvider;
use App\Lsp\Features\Mix\MixLinkProvider;
use App\Lsp\Features\Paths\PathLinkProvider;
use App\Lsp\Features\Routes\RouteCompletionProvider;
use App\Lsp\Features\Routes\RouteDiagnosticProvider;
use App\Lsp\Features\Routes\RouteHoverProvider;
use App\Lsp\Features\Routes\RouteLinkProvider;
use App\Lsp\Features\Routes\RouteParameterCompletionProvider;
use App\Lsp\Features\Storage\StorageCompletionProvider;
use App\Lsp\Features\Storage\StorageDiagnosticProvider;
use App\Lsp\Features\Storage\StorageLinkProvider;
use App\Lsp\Features\Translations\TranslationCompletionProvider;
use App\Lsp\Features\Translations\TranslationDiagnosticProvider;
use App\Lsp\Features\Translations\TranslationHoverProvider;
use App\Lsp\Features\Translations\TranslationLocaleCompletionProvider;
use App\Lsp\Features\Translations\TranslationLinkProvider;
use App\Lsp\Features\Translations\TranslationParameterCompletionProvider;
use App\Lsp\Features\Views\ViewCompletionProvider;
use App\Lsp\Features\Views\ViewContentCompletionProvider;
use App\Lsp\Features\Views\ViewDiagnosticProvider;
use App\Lsp\Features\Views\ViewHoverProvider;
use App\Lsp\Features\Views\ViewLinkProvider;

class FeatureRegistry
{
    /**
     * Create a new feature registry instance.
     */
    public function __construct(protected Workspace $workspace)
    {
        //
    }

    /**
     * Get completion providers.
     *
     * @return array<int, CompletionProvider>
     */
    public function completions(): array
    {
        return [
            new RouteParameterCompletionProvider($this->workspace),
            new RouteCompletionProvider($this->workspace),
            new ControllerActionCompletionProvider($this->workspace),
            new InertiaPropertyCompletionProvider($this->workspace),
            new InertiaCompletionProvider($this->workspace),
            new ViewContentCompletionProvider($this->workspace),
            new ViewCompletionProvider($this->workspace),
            new TranslationParameterCompletionProvider($this->workspace),
            new TranslationLocaleCompletionProvider($this->workspace),
            new TranslationCompletionProvider($this->workspace),
            new AppBindingCompletionProvider($this->workspace),
            new AssetCompletionProvider($this->workspace),
            new AuthCompletionProvider($this->workspace),
            new ConfigCompletionProvider($this->workspace),
            new EnvCompletionProvider($this->workspace),
            new MiddlewareCompletionProvider($this->workspace),
            new MixCompletionProvider($this->workspace),
            new StorageCompletionProvider($this->workspace),
        ];
    }

    /**
     * Get link providers.
     *
     * @return array<int, LinkProvider>
     */
    public function links(): array
    {
        return [
            new AppBindingLinkProvider($this->workspace),
            new AssetLinkProvider($this->workspace),
            new AuthLinkProvider($this->workspace),
            new BladeComponentLinkProvider($this->workspace),
            new ConfigLinkProvider($this->workspace),
            new ControllerActionLinkProvider($this->workspace),
            new EnvLinkProvider($this->workspace),
            new InertiaLinkProvider($this->workspace),
            new LivewireComponentLinkProvider($this->workspace),
            new MiddlewareLinkProvider($this->workspace),
            new MixLinkProvider($this->workspace),
            new PathLinkProvider($this->workspace),
            new RouteLinkProvider($this->workspace),
            new StorageLinkProvider($this->workspace),
            new TranslationLinkProvider($this->workspace),
            new ViewLinkProvider($this->workspace),
        ];
    }

    /**
     * Get hover providers.
     *
     * @return array<int, HoverProvider>
     */
    public function hovers(): array
    {
        return [
            new AppBindingHoverProvider($this->workspace),
            new AuthHoverProvider($this->workspace),
            new BladeComponentHoverProvider($this->workspace),
            new ConfigHoverProvider($this->workspace),
            new EnvHoverProvider($this->workspace),
            new InertiaHoverProvider($this->workspace),
            new LivewireComponentHoverProvider($this->workspace),
            new MiddlewareHoverProvider($this->workspace),
            new MixHoverProvider($this->workspace),
            new RouteHoverProvider($this->workspace),
            new TranslationHoverProvider($this->workspace),
            new ViewHoverProvider($this->workspace),
        ];
    }

    /**
     * Get diagnostic providers.
     *
     * @return array<int, DiagnosticProvider>
     */
    public function diagnostics(): array
    {
        return [
            new AppBindingDiagnosticProvider($this->workspace),
            new AssetDiagnosticProvider($this->workspace),
            new AuthDiagnosticProvider($this->workspace),
            new ConfigDiagnosticProvider($this->workspace),
            new ControllerActionDiagnosticProvider($this->workspace),
            new EnvDiagnosticProvider($this->workspace),
            new InertiaDiagnosticProvider($this->workspace),
            new MiddlewareDiagnosticProvider($this->workspace),
            new MixDiagnosticProvider($this->workspace),
            new RouteDiagnosticProvider($this->workspace),
            new StorageDiagnosticProvider($this->workspace),
            new TranslationDiagnosticProvider($this->workspace),
            new ViewDiagnosticProvider($this->workspace),
        ];
    }
}
