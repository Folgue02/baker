<?php
namespace App\Models\Vault;

use App\Models\Share\HasCreatedAt;
use DateTime;

/**
 * An entry of a backup created for a file.
 */
class BackupEntry
{
    use HasCreatedAt;

    public function __construct(
        string $backupPath = '',
        ?DateTime $createdAt = null
    ) {
        $this->backupPath = $backupPath;
        $this->setCreatedAt($createdAt ?? new DateTime()->format(config('baker.timestamp_format')));
    }

    /**
     * Path to the generated backup. This path must be relative to the target vault.
     */
    public string $backupPath;
}
