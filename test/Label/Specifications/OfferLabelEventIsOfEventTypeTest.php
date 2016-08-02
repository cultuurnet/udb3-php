<?php

namespace CultuurNet\UDB3\Label\Specifications;

use CultuurNet\UDB3\Event\Events\LabelAdded as EventLabelAdded;
use CultuurNet\UDB3\Event\Events\LabelDeleted as EventLabelDeleted;
use CultuurNet\UDB3\Place\Events\LabelAdded as PlaceLabelAdded;
use CultuurNet\UDB3\Place\Events\LabelDeleted as PlaceLabelDeleted;

class OfferLabelEventIsOfEventTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OfferLabelEventIsOfEventType
     */
    private $offerLabelEventIsOfEventType;

    protected function setUp()
    {
        $this->offerLabelEventIsOfEventType = new OfferLabelEventIsOfEventType();
    }

    /**
     * @test
     */
    public function it_is_satisfied_by_label_added_on_event()
    {
        $labelAdded = $this->createEvent(EventLabelAdded::class);

        $this->assertTrue($this->offerLabelEventIsOfEventType->isSatisfiedBy(
            $labelAdded
        ));
    }

    /**
     * @test
     */
    public function it_is_satisfied_by_label_deleted_from_event()
    {
        $labelDeleted = $this->createEvent(EventLabelDeleted::class);

        $this->assertTrue($this->offerLabelEventIsOfEventType->isSatisfiedBy(
            $labelDeleted
        ));
    }

    /**
     * @test
     */
    public function it_is_not_satisfied_by_label_added_on_place()
    {
        $labelAdded = $this->createEvent(PlaceLabelAdded::class);

        $this->assertFalse($this->offerLabelEventIsOfEventType->isSatisfiedBy(
            $labelAdded
        ));
    }

    /**
     * @test
     */
    public function it_is_not_satisfied_by_label_deleted_from_place()
    {
        $labelDeleted = $this->createEvent(PlaceLabelDeleted::class);

        $this->assertFalse($this->offerLabelEventIsOfEventType->isSatisfiedBy(
            $labelDeleted
        ));
    }

    /**
     * @param string $className
     * @return mixed
     */
    private function createEvent($className)
    {
        return $this->getMock($className, [], [], '', false);
    }
}
