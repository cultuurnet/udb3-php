<?php

namespace CultuurNet\UDB3\Label\Events;

use ValueObjects\Identity\UUID;

class AbstractEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var AbstractEvent
     */
    private $event;

    protected function setUp()
    {
        $this->uuid = new UUID();

        $this->event = $this->getMockForAbstractClass(
            AbstractEvent::class,
            [$this->uuid]
        );
    }

    /**
     * @test
     */
    public function it_stores_a_uuid()
    {
        $this->assertEquals($this->uuid, $this->event->getUuid());
    }

//   TODO: How to workaround the new static construct inside deserialize
//    /**
//     * @test
//     */
//    public function it_can_deserialize()
//    {
//        $actualEvent = AbstractEvent::deserialize(
//            [AbstractEvent::UUID => $this->uuid]
//        );
//
//        $this->assertEquals($this->event, $actualEvent);
//    }

    /**
     * @test
     */
    public function it_can_serialize()
    {
        $actualArray = $this->event->serialize();

        $expectedArray = [AbstractEvent::UUID => $this->uuid];

        $this->assertEquals($expectedArray, $actualArray);
    }
}
