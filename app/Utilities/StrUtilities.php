<?php

namespace App\Utilities;

final class StrUtilities
{
    private final function __construct() {}

    public static function resolveStringVariables(string $message, array $context): string
    {
        foreach ($context as $key => $value)
            $message = str_replace(":$key:", $value, $message);

        return $message;
    }

    public static function joinPaths(string $basePath, string ...$segments): string
    {
        $basePath = rtrim(str_replace('\\', '/', $basePath), '/');
        $segments = array_map(fn($seg) => trim(str_replace('\\', '/', $seg), '/'), $segments);

        return join('/', [$basePath, ...$segments]);
    }

    public static function canonicalPath(string $path, ?string $cwd = null): string
    {
        $cwd ??= getcwd();
        $path = str_replace('\\', '/', $path);

        // Join cwd and the specified path (if its not absolute)
        if (!str_starts_with($path, '/'))
            $path = self::joinPaths($cwd, $path);

        // Resolve '..' and '.'
        $segments = array_filter(explode('/', $path), fn($seg) => trim($seg) !== '');
        $resolvedSegments = [];

        foreach ($segments as $segment) {
            if ($segment === '..')
                array_pop($resolvedSegments);
            else if ($segment === '.')
                continue;
            else
                $resolvedSegments[] = $segment;
        }

        return '/' . implode('/', $resolvedSegments);
    }

    /**
     * @param string $path Path to be resolved (must be absolute and canonical).
     * @param string $base Base path (must be absolute and canonical).
     * @return ?string The relative version of the given path to the base. If the given path is not related to the
     * specified base, <code>null</code> is returned.
     */
    public static function relativePathTo(string $base, string $path): ?string
    {
        $base = self::canonicalPath($base);

        return str_starts_with($path, $base) ? substr($path, strlen($base)) : null;
    }
}
