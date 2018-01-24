<?php

namespace CultuurNet\UDB3\Address;

use ValueObjects\Geography\Country;

class LocalityAddressFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_formats_addresses()
    {
        $formatter = new LocalityAddressFormatter();

        $address = new Address(
            new Street('Martelarenlaan 1'),
            new PostalCode('3000'),
            new Locality('Leuven'),
            Country::fromNative('BE')
        );

        $expectedString = '3000 Leuven, BE';

        $this->assertEquals($expectedString, $formatter->format($address));
    }
}
