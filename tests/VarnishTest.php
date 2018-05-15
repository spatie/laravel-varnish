<?php

namespace Spatie\Varnish\Test;

use Spatie\Varnish\Varnish;

class VarnishTest extends TestCase
{
    /** @test */
    public function it_can_generate_a_ban_expression_for_a_single_host()
    {
        $expectedExpr = 'ban req.http.host ~ (^example.com$)';

        $this->assertEquals($expectedExpr, (new Varnish())->generateBanExpr(['example.com']));
    }

    /** @test */
    public function it_can_generate_a_ban_expression_for_multiple_hosts()
    {
        $expectedExpr = 'ban req.http.host ~ (^example.com$)|(^example2.com$)';

        $this->assertEquals($expectedExpr, (new Varnish())->generateBanExpr([
            'example.com',
            'example2.com',
        ]));
    }

    /** @test */
    public function it_can_generate_a_ban_command_for_a_single_host()
    {
        $expr = (new Varnish())->generateBanExpr(['example.com']);

        $expectedCommand = "sudo varnishadm -S /etc/varnish/secret -T 127.0.0.1:6082 '{$expr}'";

        $this->assertEquals($expectedCommand, (new Varnish())->generateBanCommand($expr));
    }

    /** @test */
    public function it_can_generate_a_ban_command_with_a_custom_secret_location_and_port()
    {
        $expr = (new Varnish())->generateBanExpr(['example.com']);
        $secret = '/etc/custom/secret';
        $port = 1234;

        $this->app['config']->set('varnish.administrative_secret', $secret);
        $this->app['config']->set('varnish.administrative_port', $port);

        $expectedCommand = "sudo varnishadm -S {$secret} -T 127.0.0.1:{$port} '{$expr}'";

        $this->assertEquals($expectedCommand, (new Varnish())->generateBanCommand($expr));
    }

    /** @test */
    public function it_can_generate_a_ban_command_for_multiple_hosts()
    {
        $expr = (new Varnish())->generateBanExpr([
            'example.com',
            'example2.com',
        ]);

        $expectedCommand = "sudo varnishadm -S /etc/varnish/secret -T 127.0.0.1:6082 '{$expr}'";

        $this->assertEquals($expectedCommand, (new Varnish())->generateBanCommand($expr));
    }

    /** @test */
    public function it_can_use_a_secret_from_file()
    {
        $expectedSecret = 'custom-varnish-secret';

        $tmpFile = tempnam(sys_get_temp_dir(), 'varnish-secret');
        $fp = fopen($tmpFile, 'w');
        fwrite($fp, $expectedSecret);

        $this->app['config']->set('varnish.administrative_secret_string', '');
        $this->app['config']->set('varnish.administrative_secret', $tmpFile);

        $this->assertEquals($expectedSecret, (new Varnish())->getSecret());

        fclose($fp);
        unlink($tmpFile);
    }

    /** @test */
    public function it_can_use_a_secret_from_string()
    {
        $expectedSecret = 'custom-varnish-secret';

        $this->app['config']->set('varnish.administrative_secret_string', $expectedSecret);

        $this->assertEquals($expectedSecret, (new Varnish())->getSecret());
    }
}
