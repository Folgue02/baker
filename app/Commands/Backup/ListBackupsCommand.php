<?php
namespace App\Commands\Backup;

use App\Commands\ValidatableCommand;
use App\Models\Config\BakerConfiguration;
use App\Models\Vault\BackupEntry;
use App\Models\Vault\VaultManifest;
use App\Services\ConfigService;
use App\Services\VaultService;
use App\Utilities\StrUtilities;
use App\Validation\ValidationLog;
use DateTime;
use Illuminate\Console\Scheduling\Schedule;

class ListBackupsCommand extends ValidatableCommand
{
    public function __construct(
        private ConfigService $configService,
        private VaultService $vaultService
    ) {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:list
        {filepath    : Path of the file to look for in the target vault.}
        {--s|since=  : Backup entries shown in the listing must be later than this.}
        {--u|until=  : Backup entries shown in the listing must be earlier than this.}
        {--e|exists= : Only shows entries of backups that exist (values: yes/true or no/false).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists the backups created for the specified file.';

    private ?DateTime $since = null;
    private ?DateTime $until = null;
    private ?bool $exists = null;
    private string $filepath;
    private string $ogVaultFilepath;
    private ?BakerConfiguration $bakerConfig = null;
    private ?VaultManifest $manifest = null;

    #[\Override]
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

        $selectedVault = $this->bakerConfig->getVault();

        try {
            $this->manifest = $this->vaultService->fetchOrInitializeManifest($selectedVault);
        } catch (\Exception $e) {
            $validationLog->registerError("Couldn't fetch/initialize the target vault manifest file.");
        }

        // Validate arguments
        $validationLog->startSection('Argument validation');

        $dateFormat = config('baker.date_format');
        $dateTimeFormat = config('baker.timestamp_format');
        $filepath = $this->argument('filepath');
        $since = $this->option('since');
        $until = $this->option('until');
        $exists = $this->option('exists');
        $this->exists = $this->option('exists');

        if ($since) {
            $since = DateTime::createFromFormat($dateTimeFormat, $since) ?: DateTime::createFromFormat($dateFormat, $since);

            if (!$since)
                $validationLog->registerError("The --since flag doesn't follow the valid datetime/date format (expected: $dateFormat/$dateTimeFormat)");
            else
                $this->since = $since;

        }

        if ($until) {
            $until = DateTime::createFromFormat($dateTimeFormat, $until) ?: DateTime::createFromFormat($dateFormat, $until);

            if (!$until)
                $validationLog->registerError("The --until flag doesn't follow the valid datetime/date format (expected: $dateFormat/$dateTimeFormat)");
            else
                $this->until = $until;
        }

        if ($exists) {
            $exists = match (strtolower($exists)) {
                'yes', 'true', null => true,
                'no', 'false' => false,
                default => null
            };

            if (is_null($exists)) {
                $validationLog->registerError("Invalid value specified for 'exists', only yes/true or no/false are valid");
            }

            $this->exists = $exists;
        }

        $this->filepath = StrUtilities::canonicalPath($filepath);
        $ogVaultFilepath = StrUtilities::relativePathTo($selectedVault->originRoot, $this->filepath);

        if (!$ogVaultFilepath ) {
            $validationLog->registerError("The specified file doesn't seem related to the vault ($this->filepath).");
        } else {
            $this->ogVaultFilepath = $ogVaultFilepath;
        }

        return $validationLog;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $selectedVault = $this->bakerConfig->getVault();
        $backupGroup = array_find($this->manifest->backups, fn($backup) => $backup->filepath === $this->ogVaultFilepath) ?? [];
        $backupEntries = $this->vaultService->listBackups(
            $selectedVault,
            $this->ogVaultFilepath,
            manifest: $this->manifest,
            since: $this->since,
            until: $this->until,
            exists: $this->exists
        );

        if (empty($backupGroup))
            $this->warn("No backups found for '$this->ogVaultFilepath'");
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
                $backupEntries
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
