<?php

namespace Spatie\Varnish\Test;

use Spatie\Varnish\Varnish;
use PHPUnit_Framework_TestCase;

class HelpersTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_returns_an_instance_of_varnish()
    {
        $this->assertInstanceOf(Varnish::class, varnish());
    }
}
