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
        $basePath = str_replace('\\', '/', $basePath);
        $segments = array_map(fn($seg) => trim(str_replace('\\', '/', $seg), '/'), $segments);

        return join('/', [$basePath, ...$segments]);
    }
}
