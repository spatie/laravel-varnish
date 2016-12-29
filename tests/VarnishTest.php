<?php

namespace Spatie\Varnish\Test;

use Spatie\Varnish\Varnish;
use PHPUnit_Framework_TestCase;

class VarnishTest extends TestCase
{
    /** @test */
    public function it_can_generate_a_ban_command_for_a_single_host()
    {
        $expectedCommand = "sudo varnishadm -S  -T 127.0.0.1: 'ban req.http.host ~ (^example.com$)'";


        $this->assertEquals($expectedCommand, (new Varnish())->generateBanCommand(['example.com']));
    }

    /** @test */
    public function it_can_generate_a_ban_command_for_multiple_hosts()
    {
        $expectedCommand = "sudo varnishadm -S  -T 127.0.0.1: 'ban req.http.host ~ (^example.com$)|(^example2.com$)'";

        $this->assertEquals($expectedCommand, (new Varnish())->generateBanCommand([
            'example.com',
            'example2.com'
        ]));
    }
}
