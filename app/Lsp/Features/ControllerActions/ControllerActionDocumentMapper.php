<?php

declare(strict_types=1);

namespace App\Lsp\Features\ControllerActions;

use App\Lsp\Detection\AutocompleteArgument;
use App\Lsp\Detection\DetectedArgument;
use App\Lsp\Detection\Pattern;
use App\Lsp\Features\Support\DocumentMapper;
use App\Lsp\Workspace;
use Illuminate\Support\Collection;

class ControllerActionDocumentMapper extends DocumentMapper
{
    /**
     * Create a new controller action document mapper instance.
     *
     * @param  Collection<string, array<string, mixed>>  $routes
     * @param  Collection<int, string>|null  $controllers
     */
    public function __construct(
        protected Workspace $workspace,
        protected Collection $routes,
        protected ?Collection $controllers = null,
    ) {
        //
    }

    /**
     * Get controller action detection patterns.
     *
     * @return array<int, Pattern>
     */
    protected function patterns(): array
    {
        return [
            Pattern::method(method: ['get', 'post', 'patch', 'put', 'delete', 'options'], class: Pattern::contract('Routing\\Registrar'), argument: 1),
            Pattern::method(method: 'match', class: Pattern::contract('Routing\\Registrar'), argument: 2),
            Pattern::method(method: ['get', 'post', 'patch', 'put', 'delete', 'options', 'any'], class: Pattern::facade('Route'), argument: 1),
            Pattern::method(method: ['match', 'addRoute', 'newRoute'], class: Pattern::facade('Route'), argument: 2),
            Pattern::method(method: 'fallback', class: Pattern::facade('Route'), argument: 0),
        ];
    }

    /**
     * Convert the given argument to document links.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toLinks(DetectedArgument $argument): array
    {
        $route = $this->find($argument);

        if ($route === null || ! is_string($route['filename'] ?? null)) {
            return [];
        }

        return [
            $this->workspace->link(
                $argument->range(),
                $route['filename'],
                is_numeric($route['line'] ?? null) ? (int) $route['line'] : null,
            ),
        ];
    }

    /**
     * Convert the given argument to hover.
     *
     * @param  array<string, mixed>  $position
     * @return array<string, mixed>|null
     */
    protected function toHover(DetectedArgument $argument, array $position): ?array
    {
        return null;
    }

    /**
     * Convert the given argument to diagnostics.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toDiagnostics(DetectedArgument $argument): array
    {
        $value = $argument->stringValue();

        if ($value === null || ! str_contains($value, '@') || $this->routes->has($value)) {
            return [];
        }

        return [[
            'range'    => $argument->range(),
            'severity' => 2,
            'source'   => 'Laravel Extension',
            'code'     => 'controllerAction',
            'message'  => "Controller/Method [{$value}] not found.",
        ]];
    }

    /**
     * Convert the given argument to completion items.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function toCompletions(AutocompleteArgument $argument): array
    {
        if ($argument->isArray()) {
            return [];
        }

        return ($this->controllers ?? collect())
            ->filter(fn (mixed $controller): bool => is_string($controller) && $controller !== '')
            ->map(fn (string $controller): array => [
                'label'    => $controller,
                'kind'     => 13,
                'textEdit' => [
                    'range'   => $argument->replacementRange(),
                    'newText' => $controller,
                ],
            ])
            ->values()
            ->all();
    }

    /**
     * Find the route for the given argument.
     *
     * @return array<string, mixed>|null
     */
    protected function find(DetectedArgument $argument): ?array
    {
        $route = $this->routes->get($argument->stringValue());

        return is_array($route) ? $route : null;
    }
}
