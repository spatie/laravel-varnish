<?php

namespace App\Http\Middleware;

use Closure;

class CacheWithVarnish
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        return $response->withHeaders([
            'X-Cacheable' => '1',
            'Cache-Control' => 'public, max-age='.(60 * 10),
        ]);
    }
}
