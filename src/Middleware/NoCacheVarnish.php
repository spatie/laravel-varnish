<?php

namespace Spatie\Varnish\Middleware;

use Closure;


class NoCacheVarnish
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        header('Set-Cookie: x-random=' . time());
        return $next($request);
    }
}
