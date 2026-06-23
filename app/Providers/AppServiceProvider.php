<?php

namespace App\Providers;

use App\Services\BackupService;
use App\Services\ConfigService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ConfigService::class, fn () => new ConfigService);
        $this->app->singleton(BackupService::class, fn () => new BackupService);
    }
}
