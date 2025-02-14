<?php

namespace Spatie\Varnish\Middleware;

use Closure;

class CacheWithVarnish
{
    public function handle($request, Closure $next, int $cacheTimeInMinutes = null)
    {
        $response = $next($request);

        return $response
            ->withHeaders([
                config('varnish.cacheable_header_name') => '1',
            ])
            ->setSharedMaxAge(60 * ($cacheTimeInMinutes ?? config('varnish.cache_time_in_minutes')));
    }
}
