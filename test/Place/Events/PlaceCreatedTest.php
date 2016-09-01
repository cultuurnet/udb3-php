<?php

namespace test\Place\Events;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use ValueObjects\Geography\Country;

class PlaceCreatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider serializationDataProvider
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        PlaceCreated $placeCreated
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $placeCreated->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        PlaceCreated $expectedPlaceCreated
    ) {
        $this->assertEquals(
            $expectedPlaceCreated,
            PlaceCreated::deserialize($serializedValue)
        );
    }

    public function serializationDataProvider()
    {
        return [
            [
                [
                    'place_id' => 'test 456',
                    'title' => 'title',
                    'theme' => null,
                    'address' => array(
                        'streetAddress' => 'De straat',
                        'postalCode' => '9620',
                        'addressLocality' => 'Zottegem',
                        'addressCountry' => 'BE',
                    ),
                    'calendar' => array(
                        'type' => 'permanent',
                        'startDate' => '',
                        'endDate' => '',
                        'timestamps' => array(),
                        'openingHours' => array()
                    ),
                    'event_type' => array(
                        'id' => 'bar_id',
                        'label' => 'bar',
                        'domain' => 'eventtype'
                    ),
                    'publication_date' => null
                ],
                new PlaceCreated(
                    'test 456',
                    new Title('title'),
                    new EventType('bar_id', 'bar'),
                    new Address(
                        new Street('De straat'),
                        new PostalCode('9620'),
                        new Locality('Zottegem'),
                        Country::fromNative('BE')
                    ),
                    new Calendar(
                        'permanent'
                    )
                ),
            ],
            [
                [
                    'place_id' => 'test 456',
                    'title' => 'title',
                    'theme' => [
                        'id' => '123',
                        'label' => 'foo',
                        'domain' => 'theme',
                    ],
                    'address' => array(
                        'streetAddress' => 'De straat',
                        'postalCode' => '9620',
                        'addressLocality' => 'Zottegem',
                        'addressCountry' => 'BE',
                    ),
                    'calendar' => array(
                        'type' => 'permanent',
                        'startDate' => '',
                        'endDate' => '',
                        'timestamps' => array(),
                        'openingHours' => array()
                    ),
                    'event_type' => array(
                        'id' => 'bar_id',
                        'label' => 'bar',
                        'domain' => 'eventtype'
                    ),
                    'publication_date' => null
                ],
                new PlaceCreated(
                    'test 456',
                    new Title('title'),
                    new EventType('bar_id', 'bar'),
                    new Address(
                        new Street('De straat'),
                        new PostalCode('9620'),
                        new Locality('Zottegem'),
                        Country::fromNative('BE')
                    ),
                    new Calendar(
                        'permanent'
                    ),
                    new Theme('123', 'foo')
                ),
            ],
            [
                [
                    'place_id' => 'test 456',
                    'title' => 'title',
                    'theme' => null,
                    'address' => array(
                        'streetAddress' => 'De straat',
                        'postalCode' => '9620',
                        'addressLocality' => 'Zottegem',
                        'addressCountry' => 'BE',
                    ),
                    'calendar' => array(
                        'type' => 'permanent',
                        'startDate' => '',
                        'endDate' => '',
                        'timestamps' => array(),
                        'openingHours' => array()
                    ),
                    'event_type' => array(
                        'id' => 'bar_id',
                        'label' => 'bar',
                        'domain' => 'eventtype'
                    ),
                    'publication_date' => '2016-08-01T00:00:00+0200'
                ],
                new PlaceCreated(
                    'test 456',
                    new Title('title'),
                    new EventType('bar_id', 'bar'),
                    new Address(
                        new Street('De straat'),
                        new PostalCode('9620'),
                        new Locality('Zottegem'),
                        Country::fromNative('BE')
                    ),
                    new Calendar(
                        'permanent'
                    ),
                    null,
                    \DateTimeImmutable::createFromFormat(
                        \DateTime::ISO8601,
                        '2016-08-01T00:00:00+0200'
                    )
                ),
            ],
        ];
    }
}
