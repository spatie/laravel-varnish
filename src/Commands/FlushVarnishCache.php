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
        $success_msg = 'The varnish cache has been flushed!';
        $error_msg = 'Failed to flush the varnish cache!';

        try {
            if ((new Varnish())->flush()) {
                $this->comment($success_msg);
                return true;
            } else {
                $this->error($error_msg);
            }
        } catch (\Exception $exception) {
            $this->error($error_msg);
            $this->error($exception->getMessage());
        }
        return false;
    }
}
