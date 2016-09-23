<?php

namespace CultuurNet\UDB3\PriceInfo;

use ValueObjects\Exception\InvalidNativeArgumentException;

class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_only_accepts_values_equal_or_higher_than_zero()
    {
        new Price(0);
        new Price(10.5);

        $this->setExpectedException(InvalidNativeArgumentException::class);

        new Price(-1.0);
    }
}
