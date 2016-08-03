<?php

namespace CultuurNet\UDB3\Label;

use CultuurNet\UDB3\Event\Events\LabelAdded as EventLabelAdded;
use CultuurNet\UDB3\Event\Events\LabelDeleted as EventLabelDeleted;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Place\Events\LabelAdded as PlaceLabelAdded;
use CultuurNet\UDB3\Place\Events\LabelDeleted as PlaceLabelDeleted;

class LabelEventOfferTypeResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LabelEventOfferTypeResolver
     */
    private $labelEventOfferTypeResolver;

    protected function setUp()
    {
        $this->labelEventOfferTypeResolver = new LabelEventOfferTypeResolver();
    }

    /**
     * @test
     */
    public function it_returns_offer_type_event_for_label_added_on_event()
    {
        $labelAdded = $this->createEvent(EventLabelAdded::class);

        $this->assertEquals(
            OfferType::EVENT(),
            $this->labelEventOfferTypeResolver->getOfferType($labelAdded)
        );
    }

    /**
     * @test
     */
    public function it_returns_offer_type_event_for_label_deleted_from_event()
    {
        $labelDeleted = $this->createEvent(EventLabelDeleted::class);

        $this->assertEquals(
            OfferType::EVENT(),
            $this->labelEventOfferTypeResolver->getOfferType($labelDeleted)
        );
    }

    /**
     * @test
     */
    public function it_returns_offer_type_place_for_label_added_on_place()
    {
        $labelAdded = $this->createEvent(PlaceLabelAdded::class);

        $this->assertEquals(
            OfferType::PLACE(),
            $this->labelEventOfferTypeResolver->getOfferType($labelAdded)
        );
    }

    /**
     * @test
     */
    public function it_returns_offer_type_place_for_label_deleted_from_place()
    {
        $labelDeleted = $this->createEvent(PlaceLabelDeleted::class);

        $this->assertEquals(
            OfferType::PLACE(),
            $this->labelEventOfferTypeResolver->getOfferType($labelDeleted)
        );
    }

    /**
     * @test
     */
    public function it_throws_illegal_argument_for_label_events_other_then_added_or_deleted()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $dummyLabelEvent = $this->createEvent(DummyLabelEvent::class);
        $this->labelEventOfferTypeResolver->getOfferType($dummyLabelEvent);
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
