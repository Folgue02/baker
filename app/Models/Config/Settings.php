<?php
namespace App\Models\Config;

final class Settings
{
    public string $backupSuffix;

    public static function defaultSettings(): self
    {
        $inst = new self;
        $inst->backupSuffix = "_Y-m-d_H-i";
        return $inst;
    }

    public function applySuffixToPath(string $path): string
    {
        $dirname = dirname($path);
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return $dirname . '/' . $filename . date($this->backupSuffix) . ($extension ? '.' . $extension : '');
    }
}
