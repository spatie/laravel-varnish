<?php

namespace Spatie\Varnish;

use Illuminate\Support\ServiceProvider;
use Spatie\Varnish\Commands\FlushVarnishCache;

class VarnishServiceProvider extends ServiceProvider
{
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
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-varnish.php', 'laravel-varnish');

        $this->app->bind('command.varnish:flush', FlushVarnishCache::class);

        $this->commands([
            'command.varnish:flush',
        ]);
    }
}
