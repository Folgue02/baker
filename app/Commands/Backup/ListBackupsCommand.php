<?php
namespace App\Commands\Backup;

use App\Models\Vault\BackupEntry;
use App\Services\BackupService;
use App\Services\ConfigService;
use App\Services\VaultService;
use App\Utilities\StrUtilities;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ListBackupsCommand extends Command
{
    public function __construct(
        private ConfigService $configService,
        private BackupService $backupService,
        private VaultService $vaultService
    ) {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:list {filepath : Path of the file to look for in the target vault.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists the backups created for the specified file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $config = $this->configService->readConfiguration();
        } catch (\Exception $e) {
            $this->error("Couldn't read configuration file: \n{$e->getMessage()}");
            return;
        }

        $errors = $config->validateConfig();

        if (!empty($errors)) {
            $this->error("The configuration is considered invalid due to the following reasons: ");
            foreach ($errors as $error)
                $this->error("\t - {$error->value}");

            return;
        }

        $originalFilepath = StrUtilities::canonicalPath($this->argument('filepath'));
        $selectedVault = $config->getVault();

        $manifest = $this->vaultService->fetchOrInitializeManifest($selectedVault);
        $relOgFilepath = StrUtilities::relativePathTo($selectedVault->originRoot, $originalFilepath);

        if (!$relOgFilepath) {
            $this->error("The specified file doesn't seem related to the vault ($originalFilepath).");
            return;
        }

        $backupGroup = array_find($manifest->backups, fn($backup) => $backup->filepath === $relOgFilepath) ?? [];

        if (empty($backupGroup))
            $this->warn("No backups found for '$relOgFilepath'");
        else {
            $tableHeaders = ['Created at', 'Backup filepath', 'Exists?'];

            $tableContents = array_map(
                function (BackupEntry $backupEntry) use ($selectedVault)
                {
                    $existsTag = file_exists(rtrim($selectedVault->targetRoot, '/') . $backupEntry->backupPath)
                        ? '<fg=green>Yes</>'
                        : '<fg=red>No</>';

                    return [$backupEntry->createdAt, $backupEntry->backupPath, $existsTag];
                },
                $backupGroup->backups
            );

            $this->table(
                $tableHeaders,
                $tableContents
            );
        }
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
