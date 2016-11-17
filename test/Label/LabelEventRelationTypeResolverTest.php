<?php

namespace CultuurNet\UDB3\Label;

use CultuurNet\UDB3\Event\Events\LabelAdded as EventLabelAdded;
use CultuurNet\UDB3\Event\Events\LabelDeleted as EventLabelDeleted;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use CultuurNet\UDB3\Organizer\Events\LabelAdded as OrganizerLabelAdded;
use CultuurNet\UDB3\Organizer\Events\LabelRemoved as OrganizerLabelRemoved;
use CultuurNet\UDB3\Place\Events\LabelAdded as PlaceLabelAdded;
use CultuurNet\UDB3\Place\Events\LabelDeleted as PlaceLabelDeleted;

class LabelEventRelationTypeResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LabelEventRelationTypeResolver
     */
    private $labelEventRelationTypeResolver;

    protected function setUp()
    {
        $this->labelEventRelationTypeResolver = new LabelEventRelationTypeResolver();
    }

    /**
     * @test
     */
    public function it_returns_relation_type_event_for_label_added_on_event()
    {
        $labelAdded = $this->createEvent(EventLabelAdded::class);

        $this->assertEquals(
            RelationType::EVENT(),
            $this->labelEventRelationTypeResolver->getRelationType($labelAdded)
        );
    }

    /**
     * @test
     */
    public function it_returns_relation_type_event_for_label_deleted_from_event()
    {
        $labelDeleted = $this->createEvent(EventLabelDeleted::class);

        $this->assertEquals(
            RelationType::EVENT(),
            $this->labelEventRelationTypeResolver->getRelationType($labelDeleted)
        );
    }

    /**
     * @test
     */
    public function it_returns_relation_type_place_for_label_added_on_place()
    {
        $labelAdded = $this->createEvent(PlaceLabelAdded::class);

        $this->assertEquals(
            RelationType::PLACE(),
            $this->labelEventRelationTypeResolver->getRelationType($labelAdded)
        );
    }

    /**
     * @test
     */
    public function it_returns_relation_type_place_for_label_deleted_from_place()
    {
        $labelDeleted = $this->createEvent(PlaceLabelDeleted::class);

        $this->assertEquals(
            RelationType::PLACE(),
            $this->labelEventRelationTypeResolver->getRelationType($labelDeleted)
        );
    }

    /**
     * @test
     */
    public function it_returns_relation_type_organizer_for_label_added_on_organizer()
    {
        $labelAdded = $this->createEvent(OrganizerLabelAdded::class);

        $this->assertEquals(
            RelationType::ORGANIZER(),
            $this->labelEventRelationTypeResolver->getRelationType($labelAdded)
        );
    }

    /**
     * @test
     */
    public function it_returns_relation_type_organizer_for_label_removed_from_organizer()
    {
        $labelDeleted = $this->createEvent(OrganizerLabelRemoved::class);

        $this->assertEquals(
            RelationType::ORGANIZER(),
            $this->labelEventRelationTypeResolver->getRelationType($labelDeleted)
        );
    }

    /**
     * @test
     */
    public function it_throws_illegal_argument_for_label_events_other_then_added_or_deleted()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $dummyLabelEvent = $this->createEvent(DummyLabelEvent::class);
        $this->labelEventRelationTypeResolver->getRelationType($dummyLabelEvent);
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
