<?php

namespace CultuurNet\UDB3\Label;

use CultuurNet\UDB3\Event\Events\LabelAdded as EventLabelAdded;
use CultuurNet\UDB3\Event\Events\LabelDeleted as EventLabelDeleted;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use CultuurNet\UDB3\Place\Events\LabelAdded as PlaceLabelAdded;
use CultuurNet\UDB3\Place\Events\LabelDeleted as PlaceLabelDeleted;

class LabelEventRelationTypeResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LabelEventRelationTypeResolver
     */
    private $labelEventOfferTypeResolver;

    protected function setUp()
    {
        $this->labelEventOfferTypeResolver = new LabelEventRelationTypeResolver();
    }

    /**
     * @test
     */
    public function it_returns_offer_type_event_for_label_added_on_event()
    {
        $labelAdded = $this->createEvent(EventLabelAdded::class);

        $this->assertEquals(
            RelationType::EVENT(),
            $this->labelEventOfferTypeResolver->getRelationType($labelAdded)
        );
    }

    /**
     * @test
     */
    public function it_returns_offer_type_event_for_label_deleted_from_event()
    {
        $labelDeleted = $this->createEvent(EventLabelDeleted::class);

        $this->assertEquals(
            RelationType::EVENT(),
            $this->labelEventOfferTypeResolver->getRelationType($labelDeleted)
        );
    }

    /**
     * @test
     */
    public function it_returns_offer_type_place_for_label_added_on_place()
    {
        $labelAdded = $this->createEvent(PlaceLabelAdded::class);

        $this->assertEquals(
            RelationType::PLACE(),
            $this->labelEventOfferTypeResolver->getRelationType($labelAdded)
        );
    }

    /**
     * @test
     */
    public function it_returns_offer_type_place_for_label_deleted_from_place()
    {
        $labelDeleted = $this->createEvent(PlaceLabelDeleted::class);

        $this->assertEquals(
            RelationType::PLACE(),
            $this->labelEventOfferTypeResolver->getRelationType($labelDeleted)
        );
    }

    /**
     * @test
     */
    public function it_throws_illegal_argument_for_label_events_other_then_added_or_deleted()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $dummyLabelEvent = $this->createEvent(DummyLabelEvent::class);
        $this->labelEventOfferTypeResolver->getRelationType($dummyLabelEvent);
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
