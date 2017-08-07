<?php

namespace Event\Commands;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Event\Commands\UpdateLocation;
use CultuurNet\UDB3\Location\Location;
use ValueObjects\Geography\Country;
use ValueObjects\StringLiteral\StringLiteral;

class UpdateLocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $eventId;

    /**
     * @var Location
     */
    private $location;

    /**
     * @var UpdateLocation
     */
    private $updateLocation;

    protected function setUp()
    {
        $this->eventId = '3ed90f18-93a3-4340-981d-12e57efa0211';

        $this->location = new Location(
            '57738178-28a5-4afb-90c0-fd0beba172a8',
            new StringLiteral('Het Depot'),
            new Address(
                new Street('Martelarenplein 1'),
                new PostalCode('3000'),
                new Locality('Leuven'),
                Country::fromNative('BE')
            )
        );

        $this->updateLocation = new UpdateLocation(
            $this->eventId,
            $this->location
        );
    }

    /**
     * @test
     */
    public function it_stores_an_event_id()
    {
        $this->assertEquals($this->eventId, $this->updateLocation->getItemId());
    }

    /**
     * @test
     */
    public function it_stores_a_location()
    {
        $this->assertEquals($this->location, $this->updateLocation->getLocation());
    }
}
