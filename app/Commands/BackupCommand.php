<?php

namespace App\Commands;

use App\Services\BackupService;
use App\Services\ConfigService;
use App\Services\VaultService;
use App\Utilities\StrUtilities;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class BackupCommand extends Command
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

    public function __construct(
        public ConfigService $configService,
        public BackupService $backupService,
        public VaultService $vaultService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            $config = $this->configService->readConfiguration();
        } catch (\Exception $e) {
            $this->error("Couldn't read configuration file: \n{$e->getMessage()}");
            return;
        }

        $errors = $config->validate();

        if (!empty($errors)) {
            $this->error("The configuration is considered invalid due to the following reasons: ");
            foreach ($errors as $error)
                $this->error("\t - {$error}");

            return;
        }

        $filepath = StrUtilities::canonicalPath($this->argument('filepath'));
        $selectedVault = $config->getVault();
        $settings = $config->getSettings($selectedVault->name);

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

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
