<?php

namespace App\Services;

use App\Models\Config\Settings;
use App\Models\Config\Vault;

interface IBackupService
{
    function backupFile(Vault $vault, Settings $settings, string $filepath): string;
}
