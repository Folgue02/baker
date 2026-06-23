<?php
namespace App\Models\Config;

enum ConfigErrors: string
{
    case NO_VAULTS = 'There are no vaults listed in the configuration.';
    case NO_SELECTED_VAULT = 'No vault selected in the configuration';
    case INVALID_VAULT_SELECTED = 'The selected vault is not listed in the configuration';
    case VAULT_ORIGIN_MISSING = 'The origin root of the selected vault doesn\'t exist (or is not a directory)';
    case VAULT_TARGET_MISSING = 'The target root of the selected vault doesn\'t exist (or is not a directory)';
}
