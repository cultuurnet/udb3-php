<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Role\ValueObjects\Permission;

class CopyEventTest extends \PHPUnit_Framework_TestCase
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
     * @var CopyEvent
     */
    private $copyEvent;

    protected function setUp()
    {
        $this->eventId = 'e49430ca-5729-4768-8364-02ddb385517a';

        $this->originalEventId = '27105ae2-7e1c-425e-8266-4cb86a546159';

        $this->calendar = new Calendar(
            CalendarType::SINGLE(),
            new \DateTime()
        );

        $this->copyEvent = new CopyEvent(
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
            $this->copyEvent->getItemId()
        );
    }

    /**
     * @test
     */
    public function it_stores_an_original_event_id()
    {
        $this->assertEquals(
            $this->originalEventId,
            $this->copyEvent->getOriginalEventUuid()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_calendar()
    {
        $this->assertEquals(
            $this->calendar,
            $this->copyEvent->getCalendar()
        );
    }

    /**
     * @test
     */
    public function it_has_permission_aanbod_bewerken()
    {
        $this->assertEquals(
            Permission::AANBOD_BEWERKEN(),
            $this->copyEvent->getPermission()
        );
    }
}
