<?php

class LspHelper
{
    public static function relativePath($path)
    {
        if (! str_contains($path, base_path())) {
            return (string) $path;
        }

        return ltrim(str_replace(base_path(), '', realpath($path) ?: $path), DIRECTORY_SEPARATOR);
    }

    public static function isVendor($path)
    {
        return str_contains($path, base_path('vendor'));
    }
}
