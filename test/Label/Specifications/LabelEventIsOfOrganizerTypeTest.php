<?php

namespace CultuurNet\UDB3\Label\Specifications;

use CultuurNet\UDB3\Organizer\Events\LabelAdded as OrganizerLabelAdded;
use CultuurNet\UDB3\Organizer\Events\LabelRemoved as OrganizerLabelRemoved;
use CultuurNet\UDB3\Place\Events\LabelAdded as PlaceLabelAdded;
use CultuurNet\UDB3\Place\Events\LabelDeleted as PlaceLabelDeleted;

class LabelEventIsOfOrganizerTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LabelEventIsOfOrganizerType
     */
    private $labelEventIsOfOrganizerType;

    protected function setUp()
    {
        $this->labelEventIsOfOrganizerType = new LabelEventIsOfOrganizerType();
    }

    /**
     * @test
     */
    public function it_is_satisfied_by_label_added_on_event()
    {
        $labelAdded = $this->createEvent(OrganizerLabelAdded::class);

        $this->assertTrue($this->labelEventIsOfOrganizerType->isSatisfiedBy(
            $labelAdded
        ));
    }

    /**
     * @test
     */
    public function it_is_satisfied_by_label_deleted_from_event()
    {
        $labelDeleted = $this->createEvent(OrganizerLabelRemoved::class);

        $this->assertTrue($this->labelEventIsOfOrganizerType->isSatisfiedBy(
            $labelDeleted
        ));
    }

    /**
     * @test
     */
    public function it_is_not_satisfied_by_label_added_on_place()
    {
        $labelAdded = $this->createEvent(PlaceLabelAdded::class);

        $this->assertFalse($this->labelEventIsOfOrganizerType->isSatisfiedBy(
            $labelAdded
        ));
    }

    /**
     * @test
     */
    public function it_is_not_satisfied_by_label_deleted_from_place()
    {
        $labelDeleted = $this->createEvent(PlaceLabelDeleted::class);

        $this->assertFalse($this->labelEventIsOfOrganizerType->isSatisfiedBy(
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
