<?php

namespace Spatie\Varnish\Test;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();

        $this->setUpDummyRoutes();
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
