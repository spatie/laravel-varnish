<?php

namespace Spatie\Varnish\Commands;

use Illuminate\Console\Command;
use Spatie\Varnish\Varnish;

class FlushVarnishCache extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'varnish:flush {url?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush the varnish cache. Optionally you can provide a regex of an url you want to flush, e.g. /nl/*.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $url = $this->argument('url');

        (new Varnish())->flush(null, $url);

        $this->comment('The varnish cache has been flushed!');
    }
}
