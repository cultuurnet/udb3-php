<?php

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use ValueObjects\Geography\Country;

class AddressUpdatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_be_serializable_and_deserializable()
    {
        $event = new AddressUpdated(
            'a9088117-5ec8-4117-8ce0-5ce27e685055',
            new Address(
                new Street('Eenmeilaan 35'),
                new PostalCode('3010'),
                new Locality('Kessel-Lo'),
                Country::fromNative('BE')
            )
        );

        $expectedArray = [
            'place_id' => 'a9088117-5ec8-4117-8ce0-5ce27e685055',
            'address' => [
                'streetAddress' => 'Eenmeilaan 35',
                'postalCode' => '3010',
                'addressLocality' => 'Kessel-Lo',
                'addressCountry' => 'BE',
            ],
        ];

        $serialized = $event->serialize();
        $deserialized = AddressUpdated::deserialize($serialized);

        $this->assertEquals($expectedArray, $serialized);
        $this->assertEquals($event, $deserialized);
    }
}
