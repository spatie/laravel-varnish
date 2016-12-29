<?php

namespace Spatie\Varnish\Test;

use Route;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Varnish\VarnishServiceProvider;

class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();

        $this->setUpDummyRoutes();
    }

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

    protected function setUpDummyRoutes()
    {
        Route::get('cache-me', function () {
            return 'cache me';
        });

        Route::get('do-no-cache-me', function () {
            return 'do not cache me';
        });
    }
}
