<?php

declare(strict_types=1);

namespace CultuurNet\UDB3\Event\Events\Status;

use CultuurNet\UDB3\Timestamp;
use DateTime;
use PHPUnit\Framework\TestCase;

class SubEventCancelledTest extends TestCase
{
    /**
     * @var SubEventCancelled
     */
    private $subEventCancelled;

    /**
     * @var array
     */
    private $subEventCancelledAsArray;

    protected function setUp(): void
    {
        $this->subEventCancelled = new SubEventCancelled(
            '376d4552-5bac-401d-a8ee-7a7fc639f25d',
            new Timestamp(
                new DateTime('2020-10-15T22:00:00+00:00'),
                new DateTime('2020-10-16T21:59:59+00:00')
            ),
            'Cancelled sub event'
        );

        $this->subEventCancelledAsArray = [
            'eventId' => '376d4552-5bac-401d-a8ee-7a7fc639f25d',
            'timestamp' => [
                'startDate' => '2020-10-15T22:00:00+00:00',
                'endDate' => '2020-10-16T21:59:59+00:00',
            ],
            'reason' => 'Cancelled sub event',
        ];
    }

    /**
     * @test
     */
    public function it_can_serialize(): void
    {
        $this->assertEquals(
            $this->subEventCancelledAsArray,
            $this->subEventCancelled->serialize()
        );
    }

    /**
     * @test
     */
    public function it_can_deserialize(): void
    {
        $this->assertEquals(
            $this->subEventCancelled,
            SubEventCancelled::deserialize($this->subEventCancelledAsArray)
        );
    }
}
