<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Location\Location;
use ValueObjects\Geography\Country;
use ValueObjects\StringLiteral\StringLiteral;

class LocationUpdatedTest extends \PHPUnit_Framework_TestCase
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
     * @var array
     */
    private $locationUpdatedAsArray;

    /**
     * @var LocationUpdated
     */
    private $locationUpdated;

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

        $this->locationUpdatedAsArray = [
            'item_id' => $this->eventId,
            'location' => [
                'cdbid' => '57738178-28a5-4afb-90c0-fd0beba172a8',
                'name' => 'Het Depot',
                'address' => [
                    'streetAddress' => 'Martelarenplein 1',
                    'postalCode' => '3000',
                    'addressLocality' => 'Leuven',
                    'addressCountry' => 'BE',
                ],
            ],
        ];

        $this->locationUpdated = new LocationUpdated(
            $this->eventId,
            $this->location
        );
    }

    /**
     * @test
     */
    public function it_stores_an_event_id()
    {
        $this->assertEquals($this->eventId, $this->locationUpdated->getItemId());
    }

    /**
     * @test
     */
    public function it_stores_a_location()
    {
        $this->assertEquals($this->location, $this->locationUpdated->getLocation());
    }

    /**
     * @test
     */
    public function it_can_serialize()
    {
        $this->assertEquals(
            $this->locationUpdatedAsArray,
            $this->locationUpdated->serialize()
        );
    }

    /**
     * @test
     */
    public function it_can_deserialize()
    {
        $this->assertEquals(
            $this->locationUpdated,
            LocationUpdated::deserialize($this->locationUpdatedAsArray)
        );
    }
}
