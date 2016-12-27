<?php

return [
    /**
     * The hostname this Laravel app is listening to.
     */
    'host' => 'example.com',

    /**
     * The location of the file containing the administrative password.
     */
    'secret' => '/etc/varnish/secret',

    /**
     * The port where the administrative tasks may be sent to.
     */
    'administrative_port' => 6082,

    /**
     * The default amount of minutes that content rendered using the `CacheWithVarnish`
     * middleware should be cached.
     */
    'cache_time_in_minutes' => 60 * 24,
];
