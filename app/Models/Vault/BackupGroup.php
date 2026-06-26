<?php
namespace App\Models\Vault;

/**
 * An entry which holds data about the backups created for a file.
 */
class BackupGroup
{
    /**
     * Path of the backed up file. This path is relative to the root of the
     * original vault.
     */
    public string $filepath;

    /**
     * List of backups created for the file of the entry.
     *
     * @var BackupEntry[]
     */
    public array $backups;
}
