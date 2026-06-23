<?php
namespace App\Commands\Config;

use App\Services\ConfigService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class DisplayCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'config:display';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Displays the current baker configuration in the system.';

    public function __construct(public ConfigService $configService) {
        parent::__construct();
    }

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
            foreach ($errors as $error) {
                $this->error("\t - {$error->value}");
            }
            return;
        }

        $this->line("=- VAULTS (" . count($config->vaults) . ") -= ");
        foreach ($config->vaults as $vault) {
            $this->line("\t- {$vault->name}" . ($vault->name === $config->selectedVaultName ? ' ✅' : ''));
            $this->line("\t\t({$vault->originRoot}) -> ({$vault->targetRoot})");
        }

        if ($config->globalSettings) {
            $this->line('');
            $this->line("=- GLOBAL SETTINGS -=");
            $this->line("\t- Backup suffix: {$config->globalSettings->backupSuffix}");
            $this->line('');
        }

        $selectedVault = $config->getVault();
        // Display selected vault's config
        $this->line("=- VAULT IN USE -=");
        $this->line("\tVAULT: " . $selectedVault->name);
        $this->line("\t\t({$vault->originRoot}) -> ({$vault->targetRoot})");
        $this->line('');

        $settings = $config->getSettings();
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
