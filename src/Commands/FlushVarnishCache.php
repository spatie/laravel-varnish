<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FlushVarnishCache extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'varnish:flush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush the varnish cache.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        varnish()->ban(config('laravel-varnish.hosts'));
    }
}
