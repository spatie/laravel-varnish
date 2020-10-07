# Making Varnish and Laravel play nice together

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-varnish.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-varnish)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/spatie/laravel-varnish/run-tests?label=tests)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-varnish.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-varnish)

This package provides an easy way to work with Varnish 4 (or 5) in Laravel. It provides a route middleware that, when applied to a route, will make sure Varnish will cache the response no matter what. The package also contains a function to flush the Varnish cache from within the application.

## Support us

[![Image](https://github-ads.s3.eu-central-1.amazonaws.com/laravel-varnish.jpg)](https://spatie.be/github-ad-click/laravel-varnish)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

We assume that you've already installed Varnish on your server. If not read [this blogpost](https://murze.be/2017/01/varnish-on-a-laravel-forge-server/) to learn how to install it.


You can install the package via composer:

``` bash
composer require spatie/laravel-varnish
```

The package will automatically register itself for Laravel 5.5+. 

If you are using Laravel < 5.5, you also need to add `Varnish\VarnishServiceProvider` to your `config/app.php` providers array:
```php
\Spatie\Varnish\VarnishServiceProvider::class
```
Next if you use Laravel you must publish the config-file with:

```bash
php artisan vendor:publish --provider="Spatie\Varnish\VarnishServiceProvider" --tag="config"
```
and if you use Lumen, you must copy `config/varnish.php` file to your application config folder.

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

In the published `varnish.php` config file you should set the `host` key to the right value.

Add the `Spatie\Varnish\Middleware\CacheWithVarnish` middleware to the route middlewares.

For Laravel:
```php
// app/Http/Kernel.php
protected $routeMiddleware = [
...
   'cacheable' => \Spatie\Varnish\Middleware\CacheWithVarnish::class,
];
```
If you are using Lumen, you need to load config file before route middleware definition to your `bootstrap/app.php`:
```php
$app->configure('varnish');
$app->routeMiddleware([
...
   'cacheable' => \Spatie\Varnish\Middleware\CacheWithVarnish::class,
]);
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
});
```

Behind the scenes the middleware will add an `X-Cacheable` and `Cache-Control` to the response. Varnish will remove all cookies from Laravel's response. So keep in mind that, because the`laravel_session` cookie will be removed as well, sessions will not work on routes were the `CacheWithVarnish` middleware is applied.

### Clearing cache from Varnish

There's an artisan command to flush the cache. This can come in handy in your deployment script.

```bash
php artisan varnish:flush
```

Under the hood flushing the cache will call the `sudo varnishadm`. To make it work without any hassle make sure the command is run by a unix user that has `sudo` rights.

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

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
