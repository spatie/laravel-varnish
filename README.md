# Making Varnish and Laravel play nice together

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-varnish.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-varnish)
[![Build Status](https://img.shields.io/travis/spatie/laravel-varnish/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-varnish)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/d884a8bb-d97c-4e9c-a2f0-3e673e80add3.svg?style=flat-square)](https://insight.sensiolabs.com/projects/d884a8bb-d97c-4e9c-a2f0-3e673e80add3)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-varnish.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-varnish)
[![StyleCI](https://styleci.io/repos/72834357/shield?branch=master)](https://styleci.io/repos/72834357)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-varnish.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-varnish)

This package provides an easy way to work with Varnish 4 (or 5) in Laravel. It provides a route middleware that, when applied to a route, will make sure Varnish will cache the response no matter what. The package also contains a function to flush the Varnish cache from within the application.

## Postcardware

You're free to use this package (it's [MIT-licensed](LICENSE.md)), but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

The best postcards will get published on the open source page on our website.

## Installation

We assume that you've already installed Varnish on your server. If not read [this blogpost](https://murze.be/2017/01/varnish-on-a-laravel-forge-server/) to learn how to install it.


You can install the package via composer:

``` bash
composer require spatie/laravel-varnish
```

First up: registering the service provider:

```php
// config/app.php

'providers' => [
    ...
    Spatie\Varnish\VarnishServiceProvider::class,
];
```

Next you must publish the config-file with:

```bash
php artisan vendor:publish --provider="Spatie\Varnish\VarnishServiceProvider" --tag="config"
```

This is the contents of the published file:

```php
return [
    /*
     * The hostname this Laravel app is listening to.
     */
    'host' => 'example.com',

    /*
     * The location of the file containing the administrative password.
     */
    'administrative_secret' => '/etc/varnish/secret',

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
```

In the published `laravel-varnish.php` config file you should set the `host` key to the right value.

Add the `Spatie\Varnish\Middleware\CacheWithVarnish` middleware to the route middelwares:

```php
// app/Http/Kernel.php

protected $routeMiddleware = [
...
   'cacheable' => \Spatie\Varnish\Middleware\CacheWithVarnish::class,
];
```

Finally, you should add these lines to the `vcl_backend_response` function in your VCL (by default this is located at `/etc/varnish/default.vcl` on your server):

```
if (beresp.http.X-Cacheable ~ "1") {
    unset beresp.http.set-cookie;
}
```

We highly recommend using the VCL provided [the varnish-5.0-configuration-templates repo](https://github.com/mattiasgeniar/varnish-5.0-configuration-templates) made by [Mattias Geniar](https://github.com/mattiasgeniar).

## Usage

### Caching responses

The routes whose response should be cached should use the `cacheable` middleware.

```php
// your routes file

//will be cached by Varnish
Route::group(['middleware' => 'cacheable'], function() {
    Route::get('/', 'HomeController@index');
    Route::get('/contact', 'ContactPageController@index');
});

//won't be cached by Varnish
Route::get('do-not-cache', 'AnotherController@index');
```

The amount of minutes that Varnish should cache this content can be configured in the `cache_time_in_minutes` key in the `laravel-varnish.php` config file. Alternatively you could also use a middleware parameter to specify that value.

```php

// Varnish will cache the responses of the routes inside the group for 15 minutes
Route::group(['middleware' => 'cacheable:15'], function() {
   ...
)};
```

Behind the scenes the middleware will add an `X-Cacheable` and `Cache-Control` to the response. Varnish will remove all cookies from Laravel's response. So keep in mind that, because the`laravel_session` cookie will be removed as well, sessions will not work on routes were the `CacheWithVarnish` middleware is applied.

### Clearing cache from Varnish

There's an artisan command to flush the cache. This can come in handy in your deployment script.

```bash
php artisan varnish:flush
```

You can also do this in your code to flush the cache:

```php
(new Spatie\Varnish\Varnish())->flush();
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## About Spatie
Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
