<?php

declare(strict_types=1);

namespace CultuurNet\UDB3\Event\ValueObjects;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Timestamp;
use DateTime;
use PHPUnit\Framework\TestCase;

class SubEventsTest extends TestCase
{
    /**
     * @var Calendar
     */
    private $calendarMultipleType;

    protected function setUp(): void
    {
        $this->calendarMultipleType = new Calendar(
            CalendarType::SINGLE(),
            new DateTime('2020-10-15T22:00:00+00:00'),
            new DateTime('2020-10-20T21:59:59+00:00'),
            [
                new Timestamp(
                    new DateTime('2020-10-15T22:00:00+00:00'),
                    new DateTime('2020-10-16T21:59:59+00:00')
                ),
                new Timestamp(
                    new DateTime('2020-10-17T22:00:00+00:00'),
                    new DateTime('2020-10-18T21:59:59+00:00')
                ),
                new Timestamp(
                    new DateTime('2020-10-19T22:00:00+00:00'),
                    new DateTime('2020-10-20T21:59:59+00:00')
                ),
            ]
        );
    }

    /**
     * @test
     */
    public function it_can_be_created_empty(): void
    {
        $emptySubEvents = SubEvents::createEmpty();

        $this->assertEmpty($emptySubEvents->getSubEvents());
    }

    /**
     * @test
     */
    public function it_can_be_created_from_calendar(): void
    {
        $this->assertEquals(
            [
                new SubEvent(
                    new Timestamp(
                        new DateTime('2020-10-15T22:00:00+00:00'),
                        new DateTime('2020-10-16T21:59:59+00:00')
                    ),
                    Status::scheduled()
                ),
                new SubEvent(
                    new Timestamp(
                        new DateTime('2020-10-17T22:00:00+00:00'),
                        new DateTime('2020-10-18T21:59:59+00:00')
                    ),
                    Status::scheduled()
                ),
                new SubEvent(
                    new Timestamp(
                        new DateTime('2020-10-19T22:00:00+00:00'),
                        new DateTime('2020-10-20T21:59:59+00:00')
                    ),
                    Status::scheduled()
                ),
            ],
            SubEvents::createFromCalendar($this->calendarMultipleType)->getSubEvents()
        );
    }

    /**
     * @test
     * @dataProvider subEventProvider
     */
    public function it_can_check_if_sub_event_is_present(SubEvent $subEvent, bool $equal): void
    {
        $subEvents = SubEvents::createFromCalendar($this->calendarMultipleType);

        $this->assertEquals(
            $equal,
            $subEvents->hasSubEvent($subEvent)
        );
    }

    public function subEventProvider(): array
    {
        return [
            'equal sub event' => [
                new SubEvent(
                    new Timestamp(
                        new DateTime('2020-10-15T22:00:00+00:00'),
                        new DateTime('2020-10-16T21:59:59+00:00')
                    ),
                    Status::scheduled()
                ),
                true,
            ],
            'sub event different status' => [
                new SubEvent(
                    new Timestamp(
                        new DateTime('2020-10-15T22:00:00+00:00'),
                        new DateTime('2020-10-16T21:59:59+00:00')
                    ),
                    Status::postponed()
                ),
                false,
            ],
            'sub event different timestamp' => [
                new SubEvent(
                    new Timestamp(
                        new DateTime('2020-10-15T22:00:00+00:00'),
                        new DateTime('2020-10-18T21:59:59+00:00')
                    ),
                    Status::scheduled()
                ),
                false,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider timestampProvider
     */
    public function it_can_check_if_sub_event_with_timestamp_is_present(Timestamp $timestamp, bool $equal): void
    {
        $subEvents = SubEvents::createFromCalendar($this->calendarMultipleType);

        $this->assertEquals(
            $equal,
            $subEvents->hasSubEventWithTimestamp($timestamp)
        );
    }

    public function timestampProvider(): array
    {
        return [
            'equal timestamp' => [
                new Timestamp(
                    new DateTime('2020-10-15T22:00:00+00:00'),
                    new DateTime('2020-10-16T21:59:59+00:00')
                ),
                true,
            ],
            'different timestamp' => [
                new Timestamp(
                    new DateTime('2020-10-15T22:00:00+00:00'),
                    new DateTime('2020-10-18T21:59:59+00:00')
                ),
                false,
            ],
        ];
    }

    /**
     * @test
     */
    public function it_can_add_a_sub_event(): void
    {
        $subEvent = new SubEvent(
            new Timestamp(
                new DateTime('2020-10-15T22:00:00+00:00'),
                new DateTime('2020-10-16T21:59:59+00:00')
            ),
            Status::scheduled()
        );
        $subEventDifferentTimestamp = new SubEvent(
            new Timestamp(
                new DateTime('2020-10-17T22:00:00+00:00'),
                new DateTime('2020-10-18T21:59:59+00:00')
            ),
            Status::scheduled()
        );
        $subEventDifferentStatus = new SubEvent(
            new Timestamp(
                new DateTime('2020-10-15T22:00:00+00:00'),
                new DateTime('2020-10-16T21:59:59+00:00')
            ),
            Status::postponed()
        );
        $subEventDifferentTimestampAndStatus = new SubEvent(
            new Timestamp(
                new DateTime('2020-10-18T22:00:00+00:00'),
                new DateTime('2020-10-19T21:59:59+00:00')
            ),
            Status::postponed()
        );
        $sameSubEvent = new SubEvent(
            new Timestamp(
                new DateTime('2020-10-15T22:00:00+00:00'),
                new DateTime('2020-10-16T21:59:59+00:00')
            ),
            Status::scheduled()
        );

        $subEvents = SubEvents::createEmpty();
        $subEvents->addSubEvent($subEvent);
        $subEvents->addSubEvent($subEventDifferentTimestamp);
        $subEvents->addSubEvent($subEventDifferentStatus);
        $subEvents->addSubEvent($subEventDifferentTimestampAndStatus);
        $subEvents->addSubEvent($sameSubEvent);

        $this->assertEquals(
            [
                $subEvent,
                $subEventDifferentTimestamp,
                $subEventDifferentTimestampAndStatus,
            ],
            $subEvents->getSubEvents()
        );
    }

    /**
     * @test
     */
    public function it_can_update_a_sub_event_status(): void
    {
        $subEvent = new SubEvent(
            new Timestamp(
                new DateTime('2020-10-15T22:00:00+00:00'),
                new DateTime('2020-10-16T21:59:59+00:00')
            ),
            Status::scheduled()
        );
        $differentSubEvent = new SubEvent(
            new Timestamp(
                new DateTime('2020-10-18T22:00:00+00:00'),
                new DateTime('2020-10-19T21:59:59+00:00')
            ),
            Status::postponed()
        );

        $subEvents = SubEvents::createEmpty();
        $subEvents->addSubEvent($subEvent);
        $subEvents->addSubEvent($differentSubEvent);

        $subEvents->updateSubEvent(
            new SubEvent(
                new Timestamp(
                    new DateTime('2020-10-18T22:00:00+00:00'),
                    new DateTime('2020-10-19T21:59:59+00:00')
                ),
                Status::scheduled()
            )
        );

        $this->assertEquals(
            [
                $subEvent,
                new SubEvent(
                    new Timestamp(
                        new DateTime('2020-10-18T22:00:00+00:00'),
                        new DateTime('2020-10-19T21:59:59+00:00')
                    ),
                    Status::scheduled()
                ),
            ],
            $subEvents->getSubEvents()
        );
    }
}
