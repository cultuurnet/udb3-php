<?php

namespace CultuurNet\UDB3\Event\ValueObjects;

class LocationIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_requires_a_non_empty_string_value()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('LocationId can\'t have an empty value.');

        new LocationId('');
    }
}
