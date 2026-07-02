<?php
namespace App\Commands\Config;

use App\Commands\ValidatableCommand;
use App\Models\Config\BakerConfiguration;
use App\Services\ConfigService;
use App\Validation\ValidationLog;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Override;

class DisplayCommand extends ValidatableCommand
{
    protected $signature = 'config:display';
    protected $description = 'Displays the current baker configuration in the system.';

    private BakerConfiguration $bakerConfig;

    public function __construct(public ConfigService $configService) {
        parent::__construct();
    }

    #[Override]
    protected function initializeCommand(): ValidationLog
    {
        $validationLog = new ValidationLog;

        // Read configuration
        $validationLog->startSection('Read configuration');
        try {
            $this->bakerConfig = $this->configService->readConfiguration();
        } catch (\Exception $e) {
            return $validationLog->registerError("Couldn't read configuration file: {$e->getMessage()}");
        }

        $validationLog->startSection('Validate configuration');
        $validationLog->validate($this->bakerConfig);

        return $validationLog;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->line("=- VAULTS (" . count($this->bakerConfig->vaults) . ") -= ");
        foreach ($this->bakerConfig->vaults as $vault) {
            $this->line("\t- {$vault->name}" . ($vault->name === $this->bakerConfig->selectedVaultName ? ' ✅' : ''));
            $this->line("\t\t({$vault->originRoot}) -> ({$vault->targetRoot})");
        }

        if ($this->bakerConfig->globalSettings) {
            $this->line('');
            $this->line("=- GLOBAL SETTINGS -=");
            $this->line("\t- Backup suffix: {$this->bakerConfig->globalSettings->backupSuffix}");
            $this->line('');
        }

        $selectedVault = $this->bakerConfig->getVault();
        // Display selected vault's config
        $this->line("=- VAULT IN USE -=");
        $this->line("\tVAULT: " . $selectedVault->name);
        $this->line("\t\t({$selectedVault->originRoot}) -> ({$selectedVault->targetRoot})");
        $this->line('');

        $settings = $this->bakerConfig->getSettings();
        if ($selectedVault->settings)
            $this->line("\tVAULT SETTINGS IN USE");
        else
            $this->line("\tGLOBAL/DEFAULT SETTINGS IN USE");

        $this->line("\t\t- Backup file suffix pattern: {$settings->backupSuffix}");
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
