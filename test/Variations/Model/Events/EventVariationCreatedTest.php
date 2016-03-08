<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Model\Events;

use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;

class EventVariationCreatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_sets_property_values_on_creation()
    {
        $eventVariationCreated = new OfferVariationCreated(
            new Id('29d6d973-ca78-4561-b593-631502c74a8c'),
            new Url('//beta.uitdatabank.be/event/xyz'),
            new OwnerId('b7159c3d-8ba2-499c-b4ca-01767a95625d'),
            new Purpose('personal'),
            new Description('my custom description'),
            OfferType::fromNative('Event')
        );

        $this->assertEquals(
            new Id('29d6d973-ca78-4561-b593-631502c74a8c'),
            $eventVariationCreated->getId()
        );

        $this->assertEquals(
            new Url('//beta.uitdatabank.be/event/xyz'),
            $eventVariationCreated->getEventUrl()
        );

        $this->assertEquals(
            new OwnerId('b7159c3d-8ba2-499c-b4ca-01767a95625d'),
            $eventVariationCreated->getOwnerId()
        );

        $this->assertEquals(
            new Purpose('personal'),
            $eventVariationCreated->getPurpose()
        );

        $this->assertEquals(
            new Description('my custom description'),
            $eventVariationCreated->getDescription()
        );

        $this->assertEquals(
            OfferType::fromNative('Event'),
            $eventVariationCreated->getOfferType()
        );
    }

    /**
     * @test
     */
    public function it_supports_serialization()
    {
        $eventVariationCreated = new OfferVariationCreated(
            new Id('29d6d973-ca78-4561-b593-631502c74a8c'),
            new Url('//beta.uitdatabank.be/event/xyz'),
            new OwnerId('b7159c3d-8ba2-499c-b4ca-01767a95625d'),
            new Purpose('personal'),
            new Description('my custom description'),
            OfferType::fromNative('Event')
        );

        $serialized = $eventVariationCreated->serialize();

        $deserialized = OfferVariationCreated::deserialize($serialized);

        $this->assertEquals(
            $eventVariationCreated,
            $deserialized
        );
    }
}
