<?php

use Illuminate\Support\Facades\Route;
use Spatie\Varnish\Middleware\CacheWithVarnish;

function getRoute()
{
    return Route::get('cache-me', function () {
        return 'cache me';
    });
}

it('adds headers to a response signaling that it may be cached')
    ->tap(fn () => getRoute()->middleware(CacheWithVarnish::class))
    ->get('/cache-me')
    ->assertHeader('X-Cacheable', '1')
    ->assertHeader('Cache-Control', 'public, s-maxage=86400');

it('uses the config value to determine the name of the header')
    ->tap(fn () => config()->set('varnish.cacheable_header_name', 'X-My-Custom-Header'))
    ->tap(fn () => getRoute()->middleware(CacheWithVarnish::class))
    ->get('/cache-me')
    ->assertHeader('X-My-Custom-Header', '1')
    ->assertHeader('Cache-Control', 'public, s-maxage=86400');

it('uses the config value to determine the max age')
    ->tap(fn () => config()->set('varnish.cache_time_in_minutes', 5))
    ->tap(fn () => getRoute()->middleware(CacheWithVarnish::class))
    ->get('/cache-me')
    ->assertHeader('X-Cacheable', '1')
    ->assertHeader('Cache-Control', 'public, s-maxage=300');

it('accepts an argument to determine the max age')
    ->tap(fn () => getRoute()->middleware(CacheWithVarnish::class . ':10'))
    ->get('/cache-me')
    ->assertHeader('X-Cacheable', '1')
    ->assertHeader('Cache-Control', 'public, s-maxage=600');
