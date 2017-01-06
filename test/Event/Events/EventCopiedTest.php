<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;

class EventCopiedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $eventId;

    /**
     * @var string
     */
    private $originalEventId;

    /**
     * @var Calendar
     */
    private $calendar;

    /**
     * @var EventCopied
     */
    private $eventCopied;

    protected function setUp()
    {
        $this->eventId = 'e49430ca-5729-4768-8364-02ddb385517a';

        $this->originalEventId = '27105ae2-7e1c-425e-8266-4cb86a546159';

        $this->calendar = new Calendar(
            CalendarType::SINGLE(),
            new \DateTime()
        );

        $this->eventCopied = new EventCopied(
            $this->eventId,
            $this->originalEventId,
            $this->calendar
        );
    }

    /**
     * @test
     */
    public function it_stores_an_event_id()
    {
        $this->assertEquals(
            $this->eventId,
            $this->eventCopied->getItemId()
        );
    }

    /**
     * @test
     */
    public function it_stores_an_original_event_id()
    {
        $this->assertEquals(
            $this->originalEventId,
            $this->eventCopied->getOriginalEventUuid()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_calendar()
    {
        $this->assertEquals(
            $this->calendar,
            $this->eventCopied->getCalendar()
        );
    }

    /**
     * @test
     */
    public function it_can_serialize_to_an_array()
    {
        $this->assertEquals(
            [
                'item_id' => $this->eventId,
                'original_event_id' => $this->originalEventId,
                'calendar' => $this->calendar->serialize(),
            ],
            $this->eventCopied->serialize()
        );
    }

    /**
     * @test
     */
    public function it_can_deserialize_from_an_array()
    {
        $this->assertEquals(
            $this->eventCopied,
            EventCopied::deserialize(
                [
                    'item_id' => $this->eventId,
                    'original_event_id' => $this->originalEventId,
                    'calendar' => $this->calendar->serialize(),
                ]
            )
        );
    }
}
