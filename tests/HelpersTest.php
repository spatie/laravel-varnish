<?php

namespace Spatie\Varnish\Test;

use Spatie\Varnish\Varnish;

class HelpersTest extends TestCase
{
    /** @test */
    public function it_returns_an_instance_of_varnish()
    {
        $this->assertInstanceOf(Varnish::class, varnish());
    }
}
