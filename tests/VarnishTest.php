<?php

namespace Spatie\Varnish\Test;

use Spatie\Varnish\Varnish;

class VarnishTest extends TestCase
{
    /** @test */
    public function it_can_generate_a_ban_command_for_a_single_host()
    {
        $expectedCommand = [
            '/usr/bin/sudo',
            'varnishadm',
            '-S',
            '/etc/varnish/secret',
            '-T',
            "127.0.0.1:6082",
            "\"ban req.http.host ~ (^example.com$)\""
        ];

        $this->assertEquals($expectedCommand, (new Varnish())->generateBanCommand(['example.com']));
    }

    /** @test */
    public function it_can_generate_a_band_command_with_a_custom_secret_location_and_port()
    {
        $secret = '/etc/custom/secret';
        $port = 1234;

        $this->app['config']->set('varnish.administrative_secret', $secret);
        $this->app['config']->set('varnish.administrative_port', $port);

        $expectedCommand = [
            '/usr/bin/sudo',
            'varnishadm',
            '-S',
            $secret,
            '-T',
            "127.0.0.1:{$port}",
            "\"ban req.http.host ~ (^example.com$)\""
        ];

        $this->assertEquals($expectedCommand, (new Varnish())->generateBanCommand(['example.com']));
    }

    /** @test */
    public function it_can_generate_a_ban_command_for_multiple_hosts()
    {
        $expectedCommand = [
            '/usr/bin/sudo',
            'varnishadm',
            '-S',
            '/etc/varnish/secret',
            '-T',
            "127.0.0.1:6082",
            "\"ban req.http.host ~ (^example.com$)|(^example2.com$)\""
        ];

        $this->assertEquals($expectedCommand, (new Varnish())->generateBanCommand([
            'example.com',
            'example2.com',
        ]));
    }

    /** @test */
    public function it_can_generate_a_ban_command_for_a_single_host_and_a_specific_url()
    {
        $expectedCommand = [
            '/usr/bin/sudo',
            'varnishadm',
            '-S',
            '/etc/varnish/secret',
            '-T',
            "127.0.0.1:6082",
            "\"ban req.http.host ~ (^example.com$) && req.url ~ /nl/*\""
        ];

        $this->assertEquals($expectedCommand, (new Varnish())->generateBanCommand(['example.com'], '/nl/*'));
    }

    /** @test */
    public function it_can_generate_a_ban_command_for_multiple_hosts_and_a_specific_url()
    {
        $expectedCommand = [
            '/usr/bin/sudo',
            'varnishadm',
            '-S',
            '/etc/varnish/secret',
            '-T',
            "127.0.0.1:6082",
            "\"ban req.http.host ~ (^example.com$)|(^example2.com$) && req.url ~ /nl/*\""
        ];

        $this->assertEquals($expectedCommand, (new Varnish())->generateBanCommand([
            'example.com',
            'example2.com',
        ], '/nl/*'));
    }
}
