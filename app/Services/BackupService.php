<?php

namespace App\Services;

use App\Models\Config\Settings;
use App\Models\Config\Vault;
use App\Utilities\StrUtilities;

/**
 * Service class to manage file backups.
 */
class BackupService implements IBackupService
{
    /**
     * Creates a backup of the specified file in the root of the target vault given.
     */
    public function backupFile(Vault $vault, Settings $settings, string $filepath): string
    {
        $realFilepath = realpath($filepath);
        if ($realFilepath === false || !str_starts_with($realFilepath, $vault->originRoot))
            throw new \RuntimeException("The specified '$filepath' doesn't seem to be part of the vault in use (original root: '{$vault->originRoot}') or doesn't exist.");

        $commonPath = substr($settings->applySuffixToPath($realFilepath), strlen($vault->originRoot));
        $targetFilepath = StrUtilities::joinPaths($vault->targetRoot, $commonPath);
        $targetDir = dirname($targetFilepath);
        if (!is_dir($targetDir))
            mkdir($targetDir, recursive: true);

        copy($realFilepath, $targetFilepath);

        return $targetFilepath;
    }
}
