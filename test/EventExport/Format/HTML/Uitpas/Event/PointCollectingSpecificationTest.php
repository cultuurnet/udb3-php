<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\Event;

use \CultureFeed_Uitpas_Event_CultureEvent as Event;

class PointCollectingSpecificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PointCollectingSpecification
     */
    protected $specification;

    public function setUp()
    {
        $this->specification = new PointCollectingSpecification();
    }

    /**
     * @test
     * @dataProvider satisfyingEventProvider
     * @param Event $event
     */
    public function it_is_satisfied_by_events_with_points(Event $event)
    {
        $this->specification->isSatisfiedBy($event);
    }

    public function satisfyingEventProvider()
    {
        return [
            [
                $this->createEventWithPoints(0.01),
            ],
            [
                $this->createEventWithPoints(0.2),
            ],
            [
                $this->createEventWithPoints(3.00),
            ],
            [
                $this->createEventWithPoints(4),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider unsatisfyingEventProvider
     * @param Event $event
     */
    public function it_is_unsatisfied_by_events_without_points(Event $event)
    {
        $this->specification->isSatisfiedBy($event);
    }

    public function unsatisfyingEventProvider()
    {
        return [
            [
                $this->createEventWithPoints(0),
            ],
            [
                $this->createEventWithPoints(0.00),
            ],
            [
                $this->createEventWithPoints(-1),
            ],
            [
                $this->createEventWithPoints(-1.00),
            ],
            [
                new Event(),
            ],
        ];
    }

    /**
     * @param int|float $points
     */
    protected function createEventWithPoints($points)
    {
        $event = new Event();
        $event->numberOfPoints = $points;
        return $event;
    }
}
