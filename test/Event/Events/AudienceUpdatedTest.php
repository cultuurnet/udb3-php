<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\ValueObjects\AudienceType;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

class AudienceUpdatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $itemId;

    /**
     * @var AudienceType
     */
    private $audienceType;

    /**
     * @var AudienceUpdated
     */
    private $audienceUpdated;

    protected function setUp()
    {
        $this->itemId = '6eaaa9b6-d0d2-11e6-bf26-cec0c932ce01';

        $this->audienceType = AudienceType::MEMBERS();

        $this->audienceUpdated = new AudienceUpdated(
            $this->itemId,
            $this->audienceType
        );
    }

    /**
     * @test
     */
    public function it_derives_from_abstract_event()
    {
        $this->assertInstanceOf(AbstractEvent::class, $this->audienceUpdated);
    }

    /**
     * @test
     */
    public function it_stores_an_audience_type()
    {
        $this->assertEquals(
            $this->audienceType,
            $this->audienceUpdated->getAudienceType()
        );
    }

    /**
     * @test
     */
    public function it_can_serialize_to_an_array()
    {
        $this->assertEquals(
            [
                'item_id' => $this->itemId,
                'audience_type' => $this->audienceType->toNative(),
            ],
            $this->audienceUpdated->serialize()
        );
    }

    /**
     * @test
     */
    public function it_can_deserialize_from_an_array()
    {
        $audienceUpdated = AudienceUpdated::deserialize(
            [
                'item_id' => $this->itemId,
                'audience_type' => $this->audienceType->toNative(),
            ]
        );

        $this->assertEquals($this->audienceUpdated, $audienceUpdated);
    }
}
