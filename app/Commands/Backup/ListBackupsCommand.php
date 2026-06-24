<?php
namespace App\Commands\Backup;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ListBackupsCommand extends Command
{
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
        $filepath = $this->argument('filepath');
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
