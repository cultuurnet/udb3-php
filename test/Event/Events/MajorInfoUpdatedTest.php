<?php

namespace test\Event\Events;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Event\Events\MajorInfoUpdated;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use ValueObjects\Geography\Country;
use ValueObjects\StringLiteral\StringLiteral;

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
                    'item_id' => 'test 456',
                    'title' => 'title',
                    'theme' => array(
                        'id' => 'themeid',
                        'label' => 'theme_label',
                        'domain' => 'theme'
                    ),
                    'location' => array(
                        'cdbid' => '395fe7eb-9bac-4647-acae-316b6446a85e',
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
                        '395fe7eb-9bac-4647-acae-316b6446a85e',
                        new StringLiteral('Repeteerkot'),
                        new Address(
                            new Street('Kerkstraat 69'),
                            new PostalCode('9620'),
                            new Locality('Zottegem'),
                            Country::fromNative('BE')
                        )
                    ),
                    new Calendar(
                        CalendarType::PERMANENT()
                    ),
                    new Theme('themeid', 'theme_label')
                ),
            ],
        ];
    }
}
