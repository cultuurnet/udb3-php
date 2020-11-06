<?php

declare(strict_types=1);

namespace CultuurNet\UDB3\Event\ValueObjects;

use CultuurNet\UDB3\Timestamp;
use DateTime;
use PHPUnit\Framework\TestCase;

class SubEventTest extends TestCase
{
    /**
     * @test
     * @dataProvider subEventProvider
     */
    public function it_can_check_for_equality(SubEvent $otherSubEvent, bool $equal): void
    {
        $subEvent = new SubEvent(
            new Timestamp(
                new DateTime('2016-01-03T01:01:01+01:00'),
                new DateTime('2016-01-07T01:01:01+01:00')
            ),
            Status::scheduled()
        );

        $this->assertEquals(
            $equal,
            $subEvent->equals($otherSubEvent)
        );
    }

    public function subEventProvider(): array
    {
        return [
            'equal sub event' => [
                new SubEvent(
                    new Timestamp(
                        new DateTime('2016-01-03T01:01:01+01:00'),
                        new DateTime('2016-01-07T01:01:01+01:00')
                    ),
                    Status::scheduled()
                ),
                true,
            ],
            'sub event different status' => [
                new SubEvent(
                    new Timestamp(
                        new DateTime('2016-01-03T01:01:01+01:00'),
                        new DateTime('2016-01-07T01:01:01+01:00')
                    ),
                    Status::postponed()
                ),
                false,
            ],
            'sub event different timestamp' => [
                new SubEvent(
                    new Timestamp(
                        new DateTime('2016-01-05T01:01:01+01:00'),
                        new DateTime('2016-01-07T01:01:01+01:00')
                    ),
                    Status::scheduled()
                ),
                false,
            ],
        ];
    }
}
