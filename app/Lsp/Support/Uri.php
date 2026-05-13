<?php

declare(strict_types=1);

namespace App\Lsp\Support;

use function Illuminate\Filesystem\join_paths;

class Uri
{
    /**
     * Create a new URI instance.
     */
    public function __construct(
        protected string $uri,
    ) {}

    /**
     * Create a URI instance from the given string.
     */
    public static function of(string $uri): self
    {
        return new self($uri);
    }

    /**
     * Get a URI instance with the given path appended.
     */
    public function joinPath(string $path = ''): self
    {
        return self::fromPath($this->path($path));
    }

    /**
     * Get the decoded file path, optionally with a relative path appended.
     */
    public function path(string $path = ''): string
    {
        $basePath = parse_url($this->uri, PHP_URL_PATH);

        if (!is_string($basePath)) {
            return '';
        }

        $basePath = urldecode($basePath);

        if ($path === '') {
            return $basePath;
        }

        return join_paths($basePath, $path);
    }

    /**
     * Get a path relative to this URI.
     */
    public function relativePath(string $path): string
    {
        $basePath = $this->path();

        if (!str_contains($path, $basePath)) {
            return $path;
        }

        return ltrim(str_replace($basePath, '', realpath($path) ?: $path), DIRECTORY_SEPARATOR);
    }

    /**
     * Convert the URI to a string.
     */
    public function __toString(): string
    {
        return $this->uri;
    }

    /**
     * Create a file URI instance from the given path.
     */
    public static function fromPath(string $path): self
    {
        $path = str_replace('\\', '/', $path);

        return new self('file://' . implode('/', array_map('rawurlencode', explode('/', $path))));
    }
}
