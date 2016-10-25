<?php

namespace CultuurNet\UDB3\Offer\Events\Moderation;

use CultuurNet\UDB3\Offer\Events\AbstractEvent;

class AbstractPublishedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $itemId;

    /**
     * @var \DateTimeInterface
     */
    private $embargoDate;

    /**
     * @var AbstractPublished|\PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractPublished;

    protected function setUp()
    {
        $this->itemId = '3dc2b894-9a80-11e6-9f33-a24fc0d9649c';

        $this->embargoDate = new \DateTime();

        $this->abstractPublished = $this->getMockForAbstractClass(
            AbstractPublished::class,
            [$this->itemId, $this->embargoDate]
        );
    }

    /**
     * @test
     */
    public function it_derives_from_abstract_event()
    {
        $this->assertTrue(is_subclass_of(
            $this->abstractPublished,
            AbstractEvent::class
        ));
    }

    /**
     * @test
     */
    public function it_stores_an_item_id()
    {
        $this->assertEquals(
            $this->itemId,
            $this->abstractPublished->getItemId()
        );
    }

    /**
     * @test
     */
    public function it_stores_an_embargo_date()
    {
        $this->assertEquals(
            $this->embargoDate,
            $this->abstractPublished->getEmbargoDate()
        );
    }

    /**
     * @test
     */
    public function it_can_serialize()
    {
        $expectedArray = [
            'item_id' => $this->itemId,
            'embargo_date' => $this->embargoDate->format(\DateTime::ATOM)
        ];

        $actualArray = $this->abstractPublished->serialize();

        $this->assertEquals($expectedArray, $actualArray);
    }
}
