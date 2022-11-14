<?php

use Spatie\Varnish\Varnish;

it('can generate a ban command for a single host')
    ->expect(fn () => (new Varnish())->generateBanCommand(['example.com']))
    ->toEqual("sudo varnishadm -S /etc/varnish/secret -T 127.0.0.1:6082 'ban req.http.host ~ (^example.com$)'");

it('can generate a band command with a custom secret location and port', function () {
    $secret = '/etc/custom/secret';
    $port = 1234;

    config()->set('varnish.administrative_secret', $secret);
    config()->set('varnish.administrative_port', $port);

    $expectedCommand = "sudo varnishadm -S {$secret} -T 127.0.0.1:{$port} 'ban req.http.host ~ (^example.com$)'";

    expect(
        (new Varnish())->generateBanCommand(['example.com'])
    )->toEqual($expectedCommand);
});

it('can generate a ban command for multiple hosts', function () {
    $expectedCommand = "sudo varnishadm -S /etc/varnish/secret -T 127.0.0.1:6082 'ban req.http.host ~ (^example.com$)|(^example2.com$)'";

    expect(
        (new Varnish())->generateBanCommand([
            'example.com',
            'example2.com',
        ])
    )->toEqual($expectedCommand);
});

it('can generate a ban command for a single host and a specific url')
    ->expect(fn () => (new Varnish())->generateBanCommand(['example.com'], '/nl/*'))
    ->toEqual("sudo varnishadm -S /etc/varnish/secret -T 127.0.0.1:6082 'ban req.http.host ~ (^example.com$) && req.url ~ /nl/*'");

it('can generate a ban command for multiple hosts and a specific url', function () {
    $expectedCommand = "sudo varnishadm -S /etc/varnish/secret -T 127.0.0.1:6082 'ban req.http.host ~ (^example.com$)|(^example2.com$) && req.url ~ /nl/*'";

    expect(
        (new Varnish())->generateBanCommand([
            'example.com',
            'example2.com',
        ], '/nl/*')
    )->toEqual($expectedCommand);
});
