<?php

namespace Spatie\Varnish\Test;

use Route;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Varnish\VarnishServiceProvider;

class TestCase extends Orchestra
{
    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            VarnishServiceProvider::class,
        ];
    }
}
