<?php

namespace Spatie\Varnish\Commands;

use Spatie\Varnish\Varnish;
use Illuminate\Console\Command;

class FlushVarnishCache extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'varnish:flush';

    
    private $varnish;
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush the varnish cache.';
    
    public function __construct(Varnish $varnish)
    {
        $this->varnish = $varnish;
        
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->varnish->flush();

        $this->comment('The varnish cache has been flushed!');
    }
}
