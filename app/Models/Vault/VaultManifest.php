<?php
namespace App\Models\Vault;

use App\Models\Share\HasCreatedAt;

/**
 * Data schema for vault manifests.
 * Contains entries to log generated backups.
 */
class VaultManifest
{
    use HasCreatedAt;

    public string $bakerVersion;

    /**
     * @var BackupGroup[]
     */
    public array $backups;


    /**
     * Find the backup group of the specified file, or creates an empty
     * backup group for the file.
     *
     * @param string $filepath Path to the file to backup (path must be relative to the original vault).
     * @return BackupGroup The empty backup group for the specified file (or an empty one if it didn't exist before).
     */
    public function findBackupGroupOf(string $filepath): BackupGroup
    {
         $backupGroup = array_find($this->backups, fn($backup) => $backup->filepath === $filepath);
         if (!$backupGroup) {
             $backupGroup = new BackupGroup;
             $backupGroup->filepath = $filepath;
             $backupGroup->backups = [];
             $this->backups[] = $backupGroup;
         }

         return $backupGroup;
    }

    /**
     * @return self Manifest with default values.
     */
    public static function defaultManifest(): self
    {
        $manifest = new self;
        $manifest->bakerVersion = config('app.version');
        $manifest->backups = [];
        return $manifest;
    }
}
