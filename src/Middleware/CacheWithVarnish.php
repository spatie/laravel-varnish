<?php

namespace Spatie\Varnish\CacheWithVarnish;

use Closure;

class CacheWithVarnish
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Cacheable', 1);

        return $response;
    }
}
