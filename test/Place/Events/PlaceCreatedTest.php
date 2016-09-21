<?php

namespace test\Place\Events;

use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Offer\WorkflowStatus;
use CultuurNet\UDB3\Place\Events\PlaceCreated;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use DateTimeImmutable;

class PlaceCreatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Address
     */
    private $address;

    /**
     * @var DateTimeImmutable
     */
    private $publicationDate;

    /**
     * @var PlaceCreated
     */
    private $placeCreated;

    protected function setUp()
    {
        $this->address = new Address(
            'street',
            'postal',
            'locality',
            'country'
        );

        $this->publicationDate = \DateTimeImmutable::createFromFormat(
            \DateTime::ISO8601,
            '2016-08-01T00:00:00+0200'
        );

        $this->placeCreated = new PlaceCreated(
            'id',
            new Title('title'),
            new EventType('id', 'label'),
            $this->address,
            new Calendar('permanent'),
            new Theme('id', 'label'),
            $this->publicationDate,
            WorkflowStatus::DRAFT()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_place_id()
    {
        $this->assertEquals('id', $this->placeCreated->getPlaceId());
    }

    /**
     * @test
     */
    public function it_stores_a_place_title()
    {
        $this->assertEquals(new Title('title'), $this->placeCreated->getTitle());
    }

    /**
     * @test
     */
    public function it_stores_a_place_address()
    {
        $this->assertEquals($this->address, $this->placeCreated->getAddress());
    }

    /**
     * @test
     */
    public function it_stores_a_place_calendar()
    {
        $this->assertEquals(
            new Calendar('permanent'),
            $this->placeCreated->getCalendar()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_place_publication_date()
    {
        $this->assertEquals(
            $this->publicationDate,
            $this->placeCreated->getPublicationDate()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_place_theme()
    {
        $this->assertEquals(
            new Theme('id', 'label'),
            $this->placeCreated->getTheme()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_place_workflow_status()
    {
        $this->assertEquals(
            WorkflowStatus::DRAFT(),
            $this->placeCreated->getWorkflowStatus()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param $expectedSerializedValue
     * @param PlaceCreated $placeCreated
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
     * @param $serializedValue
     * @param PlaceCreated $expectedPlaceCreated
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

    /**
     * @test
     */
    public function it_can_handle_a_missing_workflow_status_when_deserializing()
    {
        $placeCreatedAsArray = [
            'place_id' => 'test 456',
            'title' => 'title',
            'theme' => null,
            'address' => array(
                'streetAddress' => 'De straat',
                'postalCode' => '9620',
                'locality' => 'Zottegem',
                'country' => 'Belgium',
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
        ];

        $expectedPlaceCreated = new PlaceCreated(
            'test 456',
            new Title('title'),
            new EventType('bar_id', 'bar'),
            new Address(
                'De straat',
                '9620',
                'Zottegem',
                'Belgium'
            ),
            new Calendar(
                'permanent'
            )
        );

        $placeCreated = PlaceCreated::deserialize($placeCreatedAsArray);

        $this->assertEquals($expectedPlaceCreated, $placeCreated);
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
                        'locality' => 'Zottegem',
                        'country' => 'Belgium',
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
                    'publication_date' => null,
                    'workflow_status' => 'readyforvalidation'
                ],
                new PlaceCreated(
                    'test 456',
                    new Title('title'),
                    new EventType('bar_id', 'bar'),
                    new Address(
                        'De straat',
                        '9620',
                        'Zottegem',
                        'Belgium'
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
                        'locality' => 'Zottegem',
                        'country' => 'Belgium',
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
                    'publication_date' => null,
                    'workflow_status' => 'readyforvalidation'
                ],
                new PlaceCreated(
                    'test 456',
                    new Title('title'),
                    new EventType('bar_id', 'bar'),
                    new Address(
                        'De straat',
                        '9620',
                        'Zottegem',
                        'Belgium'
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
                        'locality' => 'Zottegem',
                        'country' => 'Belgium',
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
                    'publication_date' => '2016-08-01T00:00:00+0200',
                    'workflow_status' => 'readyforvalidation'
                ],
                new PlaceCreated(
                    'test 456',
                    new Title('title'),
                    new EventType('bar_id', 'bar'),
                    new Address(
                        'De straat',
                        '9620',
                        'Zottegem',
                        'Belgium'
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
            [
                [
                    'place_id' => 'test 456',
                    'title' => 'title',
                    'theme' => null,
                    'address' => array(
                        'streetAddress' => 'De straat',
                        'postalCode' => '9620',
                        'locality' => 'Zottegem',
                        'country' => 'Belgium',
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
                    'publication_date' => '2016-08-01T00:00:00+0200',
                    'workflow_status' => 'draft'
                ],
                new PlaceCreated(
                    'test 456',
                    new Title('title'),
                    new EventType('bar_id', 'bar'),
                    new Address(
                        'De straat',
                        '9620',
                        'Zottegem',
                        'Belgium'
                    ),
                    new Calendar(
                        'permanent'
                    ),
                    null,
                    \DateTimeImmutable::createFromFormat(
                        \DateTime::ISO8601,
                        '2016-08-01T00:00:00+0200'
                    ),
                    WorkflowStatus::DRAFT()
                ),
            ],
        ];
    }
}
