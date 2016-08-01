<?php

namespace CultuurNet\UDB3\Address;

use CultuurNet\UDB3\Address;

class DefaultAddressFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_formats_addresses()
    {
        $formatter = new DefaultAddressFormatter();
        
        $address = new Address(
            'Martelarenlaan 1',
            '3000',
            'Leuven',
            'BE'
        );

        $expectedString = 'Martelarenlaan 1, 3000 Leuven, BE';
        
        $this->assertEquals($expectedString, $formatter->format($address));
    }
}
