<?php
namespace App\Models\Config;

use App\Utilities\StrUtilities;
use App\Validation\IValidatable;

final class BakerConfiguration implements IValidatable
{
    public string $selectedVaultName;

    /**
     * @var Vault[] Vaults to use
     */
    public array $vaults;

    public ?Settings $globalSettings;

    #[\Override]
    public function validate(): array
    {
        $errors = [];
        $selectedVault = $this->getVault($this->selectedVaultName);

        if (!$this->selectedVaultName)
            $errors[] = ConfigErrors::NO_SELECTED_VAULT;
        else if (!$selectedVault)
            $errors[] = ConfigErrors::INVALID_VAULT_SELECTED;
        else {
            if (!is_dir($selectedVault->originRoot))
                $errors[] = ConfigErrors::VAULT_ORIGIN_MISSING;

            if (!is_dir($selectedVault->targetRoot))
                $errors[] = ConfigErrors::VAULT_TARGET_MISSING;
        }

        if (empty($this->vaults))
            $errors[] = ConfigErrors::NO_VAULTS;

        return $errors;
    }

    public function processData(): void
    {
        $envVars = getenv();
        foreach ($this->vaults as $vault) {
            $vault->name = StrUtilities::resolveStringVariables($vault->name, $envVars);
            $vault->originRoot = StrUtilities::resolveStringVariables($vault->originRoot, $envVars);
            $vault->targetRoot = StrUtilities::resolveStringVariables($vault->targetRoot, $envVars);
        }
        $this->selectedVaultName = StrUtilities::resolveStringVariables($this->selectedVaultName, $envVars);
    }

    /**
     * @param string $vaultName Name of the vault to retrieve, if not specified,
     * the `selectedVaultName` is used.
     * @return ?Vault The selected vault by the `selectedVaultName` property, or
     * the vault with the name specified.
     */
    public function getVault(?string $vaultName = null): ?Vault
    {
        $vaultName = $vaultName ?? $this->selectedVaultName;
        return array_find($this->vaults, fn($vault) => $vault->name === $vaultName);
    }

    public function getSettings(?string $vaultName = null)
    {
        $vaultName = $vaultName ?? $this->selectedVaultName;

        return $this->getVault($vaultName)->settings
            ?? ($this->globalSettings ?? Settings::defaultSettings());
    }
}
