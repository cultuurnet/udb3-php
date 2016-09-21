<?php

namespace test\Event\Events;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Event\Events\EventCreated;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Offer\WorkflowStatus;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use DateTimeImmutable;

class EventCreatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Location
     */
    private $location;

    /**
     * @var DateTimeImmutable
     */
    private $publicationDate;

    /**
     * @var EventCreated
     */
    private $eventCreated;

    protected function setUp()
    {
        $this->location = new Location(
            'id',
            'name',
            'country',
            'locality',
            'postal',
            'street'
        );

        $this->publicationDate = DateTimeImmutable::createFromFormat(
            \DateTime::ISO8601,
            '2016-08-01T00:00:00+0200'
        );

        $this->eventCreated = new EventCreated(
            'id',
            new Title('title'),
            new EventType('id', 'label'),
            $this->location,
            new Calendar('permanent'),
            new Theme('id', 'label'),
            $this->publicationDate,
            WorkflowStatus::DRAFT()
        );
    }

    /**
     * @test
     */
    public function it_stores_an_event_id()
    {
        $this->assertEquals('id', $this->eventCreated->getEventId());
    }

    /**
     * @test
     */
    public function it_stores_an_event_title()
    {
        $this->assertEquals(new Title('title'), $this->eventCreated->getTitle());
    }

    /**
     * @test
     */
    public function it_stores_an_event_location()
    {
        $this->assertEquals($this->location, $this->eventCreated->getLocation());
    }

    /**
     * @test
     */
    public function it_stores_an_event_calendar()
    {
        $this->assertEquals(
            new Calendar('permanent'),
            $this->eventCreated->getCalendar()
        );
    }

    /**
     * @test
     */
    public function it_stores_an_event_publication_date()
    {
        $this->assertEquals(
            $this->publicationDate,
            $this->eventCreated->getPublicationDate()
        );
    }

    /**
     * @test
     */
    public function it_stores_an_event_workflow_status()
    {
        $this->assertEquals(
            WorkflowStatus::DRAFT(),
            $this->eventCreated->getWorkflowStatus()
        );
    }

    /**
     * @test
     */
    public function it_stores_an_event_theme()
    {
        $this->assertEquals(
            new Theme('id', 'label'),
            $this->eventCreated->getTheme()
        );
    }

    /**
     * @test
     * @dataProvider serializationDataProvider
     * @param $expectedSerializedValue
     * @param EventCreated $eventCreated
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
     * @param $serializedValue
     * @param EventCreated $expectedEventCreated
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

    /**
     * @test
     */
    public function it_can_handle_missing_workflow_status_when_deserializing()
    {
        $eventCreatedAsArray = [
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
        ];

        $expectedEventCreated = new EventCreated(
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
        );

        $eventCreated = EventCreated::deserialize($eventCreatedAsArray);

        $this->assertEquals($expectedEventCreated, $eventCreated);
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
                    'publication_date' => null,
                    'workflow_status' => 'readyforvalidation'
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
                    'publication_date' => null,
                    'workflow_status' => 'readyforvalidation'
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
                    'publication_date' => '2016-08-01T00:00:00+0200',
                    'workflow_status' => 'readyforvalidation'
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
                    DateTimeImmutable::createFromFormat(
                        \DateTime::ISO8601,
                        '2016-08-01T00:00:00+0200'
                    )
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
                    'publication_date' => '2016-08-01T00:00:00+0200',
                    'workflow_status' => 'draft'
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
                    DateTimeImmutable::createFromFormat(
                        \DateTime::ISO8601,
                        '2016-08-01T00:00:00+0200'
                    ),
                    WorkflowStatus::DRAFT()
                ),
            ],
        ];
    }
}
