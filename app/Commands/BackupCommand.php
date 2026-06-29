<?php

namespace App\Commands;

use App\Models\Config\BakerConfiguration;
use App\Models\Config\Vault;
use App\Services\BackupService;
use App\Services\ConfigService;
use App\Services\VaultService;
use App\Utilities\StrUtilities;
use App\Validation\ValidationLog;
use Illuminate\Console\Scheduling\Schedule;
use Override;

class BackupCommand extends ValidatableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup {filepath : Path to the file to backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stores a file in the target root of the selected vault.';

    private BakerConfiguration $bakerConfig;
    private string $originalFilepath;
    private Vault $selectedVault;

    public function __construct(
        public ConfigService $configService,
        public BackupService $backupService,
        public VaultService $vaultService
    ) {
        parent::__construct();
    }

    #[Override]
    protected function initializeCommand(): ValidationLog
    {
        $validationLog = new ValidationLog;

        // ==== Read configuration
        $validationLog->startSection('Read configuration');
        try {
            $this->bakerConfig = $this->configService->readConfiguration();
        } catch (\Exception $e) {
            return $validationLog->registerError("Couldn't read configuration file: \n{$e->getMessage()}");
        }

        if (!$validationLog->validate($this->bakerConfig))
            return $validationLog;

        $this->selectedVault = $this->bakerConfig->getVault();

        // Parse arguments
        $validationLog->closeSection();
        $this->originalFilepath = StrUtilities::canonicalPath($this->argument('filepath'));

        if (!file_exists($this->originalFilepath))
            $validationLog->registerError("The specified file ($this->originalFilepath) doesn't exist or couldn't be found.");

        if (!str_starts_with($this->originalFilepath, $this->selectedVault->originRoot))
            $validationLog->registerError("The specified file ($this->originalFilepath) doesn't belong to the root of the original vault ({$this->selectedVault->originRoot})");

        return $validationLog;
    }

    public function handle(): void
    {
        $filepath = StrUtilities::canonicalPath($this->argument('filepath'));
        $selectedVault = $this->bakerConfig->getVault();
        $settings = $this->bakerConfig->getSettings($selectedVault->name);

        try {
            $backupFilepath = $this->backupService->backupFile($selectedVault, $settings, $filepath);
            $relOgFilepath = StrUtilities::relativePathTo($selectedVault->originRoot, $filepath);
            $relBakFilepath = substr($backupFilepath, strlen($selectedVault->targetRoot));

            $this->vaultService->logNewBackup($selectedVault, $relOgFilepath, $relBakFilepath);
        } catch (\Exception $e) {
            $this->error("Couldn't backup file '$filepath': " . $e->getMessage());
            return;
        }

        $this->line("File backed up: <info>$filepath</info> -> <info>$backupFilepath</info>");
    }

    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
