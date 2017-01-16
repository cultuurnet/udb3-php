<?php

namespace CultuurNet\UDB3\Location;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use ValueObjects\Geography\Country;
use ValueObjects\StringLiteral\StringLiteral;

class LocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_serialized_and_deserialized()
    {
        $originalLocation = new Location(
            '335be568-aaf0-4147-80b6-9267daafe23b',
            new StringLiteral('Repeteerkot'),
            new Address(
                new Street('Kerkstraat 69'),
                new PostalCode('9630'),
                new Locality('Zottegem'),
                Country::fromNative('BE')
            )
        );

        $serializedLocation = $originalLocation->serialize();

        $deserializedLocation = Location::deserialize($serializedLocation);

        $this->assertEquals($originalLocation, $deserializedLocation);
    }
}
