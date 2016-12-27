<?php

namespace Spatie\Varnish;

use Illuminate\Support\ServiceProvider;
use Spatie\UptimeMonitor\Commands\CheckUptime;
use Spatie\UptimeMonitor\Commands\ListMonitors;
use Spatie\UptimeMonitor\Commands\CreateMonitor;
use Spatie\UptimeMonitor\Commands\DeleteMonitor;
use Spatie\UptimeMonitor\Commands\EnableMonitor;
use Spatie\UptimeMonitor\Commands\DisableMonitor;
use Spatie\UptimeMonitor\Commands\CheckCertificates;
use Spatie\UptimeMonitor\Notifications\EventHandler;
use Spatie\UptimeMonitor\Helpers\UptimeResponseCheckers\UptimeResponseChecker;

class VarnishServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/laravel-varnish.php' => config_path('laravel-varnish.php'),
            ], 'config');
        }

    }

}
