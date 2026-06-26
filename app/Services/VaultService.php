<?php
namespace App\Services;

use App\Models\Config\Vault;
use App\Models\Vault\BackupEntry;
use App\Models\Vault\VaultManifest;
use App\Utilities\StrUtilities;
use JsonMapper;

final class VaultService
{
    public const MANIFEST_FILENAME = 'manifest.baker.json';

    public function pathToManifest(Vault $vault): string
    {
        return StrUtilities::canonicalPath(StrUtilities::joinPaths($vault->targetRoot, self::MANIFEST_FILENAME));
    }

    public function fetchOrInitializeManifest(Vault $vault): VaultManifest
    {
        $manifestPath = $this->pathToManifest($vault);

        if (!file_exists($manifestPath)) {
            // Initialize the manifest in the target vault.
            $manifest = VaultManifest::defaultManifest();
            $this->storeManifest($vault, $manifest);
        } else {
            // Read the manifest file.
            $arrManifest = json_decode(file_get_contents($manifestPath));
            $mapper = new JsonMapper;
            $manifest = $mapper->map($arrManifest, VaultManifest::class);
        }

        return $manifest;
    }

    /**
     * Stores the manifest as a JSON in the manifest path {@see $this::pathToManifest()}
     *
     * @param Vault $vault The vault's paths determine where the manifest is located at.
     * @param VaultManifest $vaultManifest The new manifest to store in the file.
     */
    public function storeManifest(Vault $vault, VaultManifest $manifest): void
    {
        $manifestPath = $this->pathToManifest($vault);
        file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT));
    }


    /**
     * Logs the new backup in the manifest of the specified vault. After doing so, the manifest is updated in the
     * disk.
     *
     * NOTE: The specified path MUST be relative to their original/target vaults
     *
     * @param Vault $vault The backup log will be stored in its manifest.
     * @param string $originalFilepath Path to the original file that has been backed up.
     * @param string $backupFilepath Path to the generated backup file.
     */
    public function logNewBackup(Vault $vault, string $originalFilepath, string $backupFilepath): void
    {
        $manifest = $this->fetchOrInitializeManifest($vault);
        $backupGroup = $manifest->findBackupGroupOf($originalFilepath);

        $backupEntry = new BackupEntry($backupFilepath);
        $backupGroup->backups[] = $backupEntry;

        $this->storeManifest($vault, $manifest);
    }
}
