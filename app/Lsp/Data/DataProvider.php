<?php

declare(strict_types=1);

namespace App\Lsp\Data;

use App\Lsp\PhpRunner;
use Symfony\Component\Finder\Glob;

abstract class DataProvider
{
    /**
     * The loaded provider data.
     */
    protected mixed $data = null;

    /**
     * Whether the provider has loaded data.
     */
    protected bool $loaded = false;

    /**
     * Create a new data provider instance.
     */
    public function __construct(
        protected ?PhpRunner $php = null,
    ) {
        //
    }

    /**
     * Get the template to run via tinker.
     */
    abstract public function template(): string;

    /**
     * Parse the raw template result.
     *
     * @param  array<string, mixed>|array<int, mixed>  $data
     */
    abstract public function parse(array $data): mixed;

    /**
     * Get file watcher patterns for this provider.
     *
     * @return array<int, string>
     */
    abstract public function patterns(): array;

    /**
     * Determine if the provider watches the given relative path.
     */
    public function matches(string $relativePath): bool
    {
        $path = '/' . ltrim(str_replace('\\', '/', $relativePath), '/');

        foreach ($this->patterns() as $pattern) {
            $pattern = '/' . ltrim(str_replace('\\', '/', $pattern), '/');

            if (preg_match(Glob::toRegex($pattern), $path) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set the PHP runner used to execute templates.
     */
    public function setPhp(PhpRunner $php): void
    {
        $this->php = $php;
    }

    /**
     * Get the parsed data for this provider.
     */
    public function get(): mixed
    {
        if (! $this->loaded) {
            $this->load();
        }

        return $this->data ?? $this->default();
    }

    /**
     * Invalidate cached data.
     */
    public function invalidate(): void
    {
        $this->loaded = false;
        $this->data = null;
    }

    /**
     * Get the default data value.
     */
    protected function default(): mixed
    {
        return [];
    }

    /**
     * Load and parse provider data.
     */
    protected function load(): void
    {
        if ($this->php === null) {
            return;
        }

        $template = $this->template();

        if ($template === '') {
            return;
        }

        $result = $this->php->json($template);

        if (! is_array($result)) {
            return;
        }

        $this->data = $this->parse($result);
        $this->loaded = true;
    }
}
