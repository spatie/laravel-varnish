<?php

namespace Spatie\Varnish\Test;

use Route;
use Spatie\Varnish\Middleware\CacheWithVarnish;

class MiddlewareTest extends TestCase
{
    /** @test */
    public function it_adds_headers_to_a_response_signaling_that_it_may_be_cached()
    {
        $this->getRoute()->middleware(CacheWithVarnish::class);

        $response = $this->visit('/cache-me');

        $response->seeHeader('X-Cacheable', '1');
        $response->seeHeader('Cache-Control', 'max-age=86400, public');
    }

    /** @test */
    public function it_uses_the_config_value_to_determine_the_name_of_the_header()
    {
        $this->app['config']->set('laravel-varnish.cacheable_header_name', 'X-My-Custom-Header');

        $this->getRoute()->middleware(CacheWithVarnish::class);

        $response = $this->visit('/cache-me');

        $response->seeHeader('X-My-Custom-Header', '1');
        $response->seeHeader('Cache-Control', 'max-age=86400, public');
    }

    /** @test */
    public function it_uses_the_config_value_to_determine_the_max_age()
    {
        $this->app['config']->set('laravel-varnish.cache_time_in_minutes', 5);

        $this->getRoute()->middleware(CacheWithVarnish::class);

        $response = $this->visit('/cache-me');

        $response->seeHeader('X-Cacheable', '1');
        $response->seeHeader('Cache-Control', 'max-age=300, public');
    }

    /** @test */
    public function it_accepts_an_argument_to_determine_the_max_age()
    {
        $this->getRoute()->middleware(CacheWithVarnish::class.':10');

        $response = $this->visit('/cache-me');

        $response->seeHeader('X-Cacheable', '1');
        $response->seeHeader('Cache-Control', 'max-age=600, public');
    }

    protected function getRoute()
    {
        return Route::get('cache-me', function () {
            return 'cache me';
        });
    }
}
