<?php

namespace test\Event\Events;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;

class MajorInfoUpdatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider serializationDataProvider
     */
    public function it_can_be_serialized_into_an_array(
        $expectedSerializedValue,
        MajorInfoUpdated $majorInfoUpdated
    ) {
        $this->assertEquals(
            $expectedSerializedValue,
            $majorInfoUpdated->serialize()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     */
    public function it_can_be_deserialized_from_an_array(
        $serializedValue,
        MajorInfoUpdated $expectedMajorInfoUpdated
    ) {
        $this->assertEquals(
            $expectedMajorInfoUpdated,
            MajorInfoUpdated::deserialize($serializedValue)
        );
    }

    public function serializationDataProvider()
    {
        return [
            'event' => [
                [
                    'event_id' => 'test 456',
                    'title' => 'title',
                    'theme' => array(
                        'id' => 'themeid',
                        'label' => 'theme_label',
                        'domain' => 'theme'
                    ),
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
                new MajorInfoUpdated(
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
                    new Theme('themeid', 'theme_label')
                ),
            ],
        ];
    }
}
