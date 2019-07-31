<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use DateTime;
use PHPUnit\Framework\TestCase;

class EventCopiedTest extends TestCase
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

        // Microseconds are not taken into account when serializing, but since
        // PHP 7.1 DateTime incorporates them. We set the microseconds
        // explicitly to 0 in this test to make it pass.
        // See http://php.net/manual/en/migration71.incompatible.php#migration71.incompatible.datetime-microseconds.
        $this->calendar = new Calendar(
            CalendarType::SINGLE(),
            new DateTime('2017-01-24T21:47:26.000000+0000')
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
            $this->eventCopied->getOriginalEventId()
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

    /**
     * @test
     */
    public function it_requires_a_string_as_event_id()
    {
        $eventId = false;
        $originalEventId = '27105ae2-7e1c-425e-8266-4cb86a546159';
        $calendar = new Calendar(
            CalendarType::SINGLE(),
            new \DateTime()
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Expected itemId to be a string, received boolean'
        );

        new EventCopied($eventId, $originalEventId, $calendar);
    }

    /**
     * @test
     */
    public function it_requires_a_string_as_original_event_id()
    {
        $eventId = 'e49430ca-5729-4768-8364-02ddb385517a';
        $originalEventId = false;
        $calendar = new Calendar(
            CalendarType::SINGLE(),
            new \DateTime()
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Expected originalEventId to be a string, received boolean'
        );

        new EventCopied($eventId, $originalEventId, $calendar);
    }
}
