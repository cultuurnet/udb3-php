<?php

namespace test\Event\Events;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use DateTimeImmutable;
use ValueObjects\Geography\Country;
use ValueObjects\String\String as StringLiteral;

class EventCreatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider serializationDataProvider
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        EventCreated $eventCreated
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $eventCreated->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        EventCreated $expectedEventCreated
    ) {
        $this->assertEquals(
            $expectedEventCreated,
            EventCreated::deserialize($serializedValue)
        );
    }

    public function serializationDataProvider()
    {
        return [
            [
                [
                    'event_id' => 'test 456',
                    'title' => 'title',
                    'theme' => null,
                    'location' => array(
                        'cdbid' => 'd379187b-7f71-4403-8fff-645a28be8fd0',
                        'name' => 'Repeteerkot',
                        'address' => array(
                            'addressCountry' => 'BE',
                            'addressLocality' => 'Zottegem',
                            'postalCode' => '9620',
                            'streetAddress' => 'Kerkstraat 69'
                        ),
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
                new EventCreated(
                    'test 456',
                    new Title('title'),
                    new EventType('bar_id', 'bar'),
                    new Location(
                        'd379187b-7f71-4403-8fff-645a28be8fd0',
                        new StringLiteral('Repeteerkot'),
                        new Address(
                            new Street('Kerkstraat 69'),
                            new PostalCode('9620'),
                            new Locality('Zottegem'),
                            Country::fromNative('BE')
                        )
                    ),
                    new Calendar(
                        'permanent'
                    )
                ),
            ],
            [
                [
                    'event_id' => 'test 456',
                    'title' => 'title',
                    'theme' => [
                        'id' => '123',
                        'label' => 'foo',
                        'domain' => 'theme',
                    ],
                    'location' => array(
                        'cdbid' => 'd379187b-7f71-4403-8fff-645a28be8fd0',
                        'name' => 'Repeteerkot',
                        'address' => array(
                            'addressCountry' => 'BE',
                            'addressLocality' => 'Zottegem',
                            'postalCode' => '9620',
                            'streetAddress' => 'Kerkstraat 69'
                        ),
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
                new EventCreated(
                    'test 456',
                    new Title('title'),
                    new EventType('bar_id', 'bar'),
                    new Location(
                        'd379187b-7f71-4403-8fff-645a28be8fd0',
                        new StringLiteral('Repeteerkot'),
                        new Address(
                            new Street('Kerkstraat 69'),
                            new PostalCode('9620'),
                            new Locality('Zottegem'),
                            Country::fromNative('BE')
                        )
                    ),
                    new Calendar(
                        'permanent'
                    ),
                    new Theme('123', 'foo')
                ),
            ],
            [
                [
                    'event_id' => 'test 456',
                    'title' => 'title',
                    'theme' => null,
                    'location' => array(
                        'cdbid' => 'd379187b-7f71-4403-8fff-645a28be8fd0',
                        'name' => 'Repeteerkot',
                        'address' => array(
                            'addressCountry' => 'BE',
                            'addressLocality' => 'Zottegem',
                            'postalCode' => '9620',
                            'streetAddress' => 'Kerkstraat 69'
                        ),
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
                new EventCreated(
                    'test 456',
                    new Title('title'),
                    new EventType('bar_id', 'bar'),
                    new Location(
                        'd379187b-7f71-4403-8fff-645a28be8fd0',
                        new StringLiteral('Repeteerkot'),
                        new Address(
                            new Street('Kerkstraat 69'),
                            new PostalCode('9620'),
                            new Locality('Zottegem'),
                            Country::fromNative('BE')
                        )
                    ),
                    new Calendar(
                        'permanent'
                    ),
                    null,
                    DateTimeImmutable::createFromFormat(
                        \DateTime::ISO8601,
                        '2016-08-01T00:00:00+0200'
                    )
                ),
            ],
        ];
    }
}
