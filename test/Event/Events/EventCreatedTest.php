<?php

namespace test\Event\Events;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use ValueObjects\DateTime\Date;
use ValueObjects\DateTime\DateTime;
use ValueObjects\DateTime\Hour;
use ValueObjects\DateTime\Minute;
use ValueObjects\DateTime\Month;
use ValueObjects\DateTime\MonthDay;
use ValueObjects\DateTime\Second;
use ValueObjects\DateTime\Time;
use ValueObjects\DateTime\Year;

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
                        'cdbid' => 'cdbid',
                        'name' => 'Repeteerkot',
                        'address' => array(
                            'addressCountry' => 'Belgium',
                            'addressLocality' => 'Zottegem',
                            'postalCode' => '9620',
                            'streetAddress' => 'De straat'
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
                        'cdbid',
                        'Repeteerkot',
                        'Belgium',
                        'Zottegem',
                        '9620',
                        'De straat'
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
                        'cdbid' => 'cdbid',
                        'name' => 'Repeteerkot',
                        'address' => array(
                            'addressCountry' => 'Belgium',
                            'addressLocality' => 'Zottegem',
                            'postalCode' => '9620',
                            'streetAddress' => 'De straat'
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
                        'cdbid',
                        'Repeteerkot',
                        'Belgium',
                        'Zottegem',
                        '9620',
                        'De straat'
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
                        'cdbid' => 'cdbid',
                        'name' => 'Repeteerkot',
                        'address' => array(
                            'addressCountry' => 'Belgium',
                            'addressLocality' => 'Zottegem',
                            'postalCode' => '9620',
                            'streetAddress' => 'De straat'
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
                    'publication_date' => '2016-08-01T00:00:00+02:00'
                ],
                new EventCreated(
                    'test 456',
                    new Title('title'),
                    new EventType('bar_id', 'bar'),
                    new Location(
                        'cdbid',
                        'Repeteerkot',
                        'Belgium',
                        'Zottegem',
                        '9620',
                        'De straat'
                    ),
                    new Calendar(
                        'permanent'
                    ),
                    null,
                    new DateTime(
                        new Date(
                            new Year(2016),
                            Month::fromNative('AUGUST'),
                            new MonthDay(1)
                        ),
                        new Time(
                            new Hour(0),
                            new Minute(0),
                            new Second(0)
                        )
                    )
                ),
            ],
        ];
    }
}
