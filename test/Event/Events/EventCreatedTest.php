<?php

namespace test\Event\Events;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Title;

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
            'event' => [
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
                    )
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
        ];
    }
}
