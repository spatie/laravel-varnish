<?php

namespace Spatie\Varnish\Test;

use PHPUnit_Framework_TestCase;
use Spatie\Varnish\Varnish;

class FunctionsTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_returns_an_instance_of_varnish()
    {
        $this->assertInstanceOf(Varnish::class, varnish());
    }
}
