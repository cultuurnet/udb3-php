<?php

namespace CultuurNet\UDB3\Event;

use \CultureFeed_Cdb_Data_Calendar_PeriodList as PeriodList;
use \CultureFeed_Cdb_Data_Calendar_Period as Period;
use \CultureFeed_Cdb_Data_Calendar_TimestampList as TimestampList;
use \CultureFeed_Cdb_Data_Calendar_Timestamp as Timestamp;
use CultuurNet\UDB3\Event\ReadModel\CalendarRepositoryInterface;
use Doctrine\Common\Cache\ArrayCache;

class EventCalendarProjectorTest extends CdbXMLProjectorTestBase
{
    /**
     * @var CalendarRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    /**
     * @var EventCalendarProjector
     */
    protected $projector;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->getMock(CalendarRepositoryInterface::class);
        $this->projector = new EventCalendarProjector($this->repository);
    }

    /**
     * @test
     */
    public function it_saves_the_calendar_periods_from_events_imported_from_udb2()
    {
        $event = $this->eventImportedFromUDB2('samples/event_with_calendar_periods.cdbxml.xml');
        $this->repositoryExpectsPeriodList();
        $this->projector->applyEventImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_saves_the_calendar_periods_from_events_updated_from_udb2()
    {
        $event = $this->eventUpdatedFromUDB2('samples/event_with_calendar_periods.cdbxml.xml');
        $this->repositoryExpectsPeriodList();
        $this->projector->applyEventUpdatedFromUDB2($event);
    }

    private function repositoryExpectsPeriodList()
    {
        $this->repository->expects($this->once())
            ->method('save')
            ->with(
                'someId',
                $this->callback(function (PeriodList $calendar) {
                    // Counting the objects in an iterator will loop over all objects and set the pointer to a non-
                    // existing object when it's done, so we need to rewind to the start of the iterator.
                    $count = iterator_count($calendar);
                    $calendar->rewind();

                    /* @var Period $period */
                    $period = $calendar->current();

                    return $count == 1 &&
                    $period->getDateFrom() == '2014-11-01' &&
                    $period->getDateTo() == '2014-11-09';
                })
            );
    }

    /**
     * @test
     */
    public function it_saves_the_calendar_timestamps_from_events_imported_from_udb2()
    {
        $event = $this->eventImportedFromUDB2('samples/event_with_calendar_timestamps.cdbxml.xml');
        $this->repositoryExpectsTimestamps();
        $this->projector->applyEventImportedFromUDB2($event);
    }

    /**
     * @test
     */
    public function it_saves_the_calendar_timestamps_from_events_updated_from_udb2()
    {
        $event = $this->eventUpdatedFromUDB2('samples/event_with_calendar_timestamps.cdbxml.xml');
        $this->repositoryExpectsTimestamps();
        $this->projector->applyEventUpdatedFromUDB2($event);
    }

    private function repositoryExpectsTimestamps()
    {
        $this->repository->expects($this->once())
            ->method('save')
            ->with(
                'someId',
                $this->callback(function (TimestampList $calendar) {
                    // Counting the objects in an iterator will loop over all objects and set the pointer to a non-
                    // existing object when it's done, so we need to rewind to the start of the iterator.
                    $count = iterator_count($calendar);
                    $calendar->rewind();

                    /* @var Timestamp $timestamp */
                    $timestamp = $calendar->current();

                    return $count == 1 &&
                        $timestamp->getDate() == '2014-11-21' &&
                        $timestamp->getStartTime() == '20:00:00';
                })
            );
    }
}
