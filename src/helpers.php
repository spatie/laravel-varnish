<?php

use Spatie\Varnish\Varnish;

function varnish(): Varnish
{
    return app(Varnish::class);
}
