<?php

namespace Spatie\Varnish;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\FlushVarnishCache;

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

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-uptime-monitor.php', 'laravel-uptime-monitor');

        $this->app->bind('command.monitor:check-uptime', FlushVarnishCache::class);

        $this->commands([
            'command.varnish:flush',
        ]);
    }
}
