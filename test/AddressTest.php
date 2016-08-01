<?php

namespace CultuurNet\UDB3;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_compare_two_addresses()
    {
        $addressLeuven = new Address(
            'Martelarenlaan 1',
            '3000',
            'Leuven',
            'BE'
        );

        $addressBrussel = new Address(
            'Wetstraat 1',
            '1000',
            'Brussel',
            'BE'
        );

        $this->assertTrue($addressLeuven->sameAs(clone $addressLeuven));
        $this->assertFalse($addressLeuven->sameAs($addressBrussel));
    }
}
