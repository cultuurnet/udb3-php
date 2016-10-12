<?php

namespace CultuurNet\UDB3\Address;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use ValueObjects\Geography\Country;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_compare_two_addresses()
    {
        $addressLeuven = new Address(
            new Street('Martelarenlaan 1'),
            new PostalCode('3000'),
            new Locality('Leuven'),
            Country::fromNative('BE')
        );

        $addressBrussel = new Address(
            new Street('Wetstraat 1'),
            new PostalCode('1000'),
            new Locality('Brussel'),
            Country::fromNative('BE')
        );

        $this->assertTrue($addressLeuven->sameAs(clone $addressLeuven));
        $this->assertFalse($addressLeuven->sameAs($addressBrussel));
    }
}
