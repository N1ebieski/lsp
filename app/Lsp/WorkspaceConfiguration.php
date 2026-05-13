<?php

declare(strict_types=1);

namespace App\Lsp;

use Illuminate\Support\Arr;
use Illuminate\Support\Traits\InteractsWithData;

class WorkspaceConfiguration
{
    use InteractsWithData;

    /**
     * Create a new workspace configuration instance.
     *
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        protected array $options = [],
    ) {
        //
    }

    /**
     * Replace the stored configuration options.
     *
     * @param  array<string, mixed>  $options
     */
    public function replace(array $options): void
    {
        $this->options = $options;
    }

    /**
     * Get the PHP command used to run Laravel scripts.
     *
     * @return string[]
     */
    public function phpCommand(): array
    {
        return (array) $this->data('phpCommand', ['php']);
    }

    /**
     * Get all stored configuration options.
     *
     * @param  array|mixed|null  $keys
     * @return array<string, mixed>
     */
    public function all($keys = null): array
    {
        if (! $keys) {
            return $this->options;
        }

        $results = [];

        foreach (is_array($keys) ? $keys : func_get_args() as $key) {
            Arr::set($results, $key, Arr::get($this->options, $key));
        }

        return $results;
    }

    /**
     * Retrieve data from the stored configuration options.
     */
    protected function data($key = null, $default = null): mixed
    {
        if ($key === null) {
            return $this->options;
        }

        return Arr::get($this->options, $key, $default);
    }
}
