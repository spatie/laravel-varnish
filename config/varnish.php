<?php

return [
    /*
     * The hostname(s) this Laravel app is listening to.
     */
    'host' => ['example.com'],

    /*
     * The execution type to be used. Allowed values are 'command' or 'socket'.
     *
     * This will determine whether `varnishadm` or the varnish administrative socket
     * is used for a local or remote varnish instance, respectively.
     */
    'execution_type' => 'command',

    /*
     * The location of the file containing the administrative password.
     */
    'administrative_secret' => '/etc/varnish/secret',

    /*
     * The actual administrative password used in your varnish configuration.
     *
     * When using `execution_type` 'command', use `administrative_secret`
     * instead, as `varnishadm` expects the secret to be a file path.
     *
     * If you are using `execution_type` 'socket', both parameters are supported, but
     * `administrative_secret_string` will take precedence over `administrative_secret`.
     */
    'administrative_secret_string' => '',

    /*
     * The host where the administrative tasks may be sent to when
     * using execution_type 'socket'.
     */
    'administrative_host' => '127.0.0.1',

    /*
     * The port where the administrative tasks may be sent to.
     */
    'administrative_port' => 6082,

    /*
     * The default amount of minutes that content rendered using the `CacheWithVarnish`
     * middleware should be cached.
     */
    'cache_time_in_minutes' => 60 * 24,

    /*
     * The name of the header that triggers Varnish to cache the response.
     */
    'cacheable_header_name' => 'X-Cacheable',
];
