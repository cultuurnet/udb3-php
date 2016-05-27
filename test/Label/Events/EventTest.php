<?php

namespace CultuurNet\UDB3\Label\Events;

use ValueObjects\Identity\UUID;

class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var Event
     */
    private $event;

    protected function setUp()
    {
        $this->uuid = new UUID();

        $this->event = new Event(
            $this->uuid
        );
    }

    /**
     * @test
     */
    public function it_stores_a_uuid()
    {
        $this->assertEquals($this->uuid, $this->event->getUuid());
    }

    /**
     * @test
     */
    public function it_can_deserialize()
    {
        $actualEvent = Event::deserialize(
            [Event::UUID => $this->uuid->toNative()]
        );

        $this->assertEquals($this->event, $actualEvent);
    }

    /**
     * @test
     */
    public function it_can_serialize()
    {
        $actualArray = $this->event->serialize();

        $expectedArray = [Event::UUID => $this->uuid->toNative()];

        $this->assertEquals($expectedArray, $actualArray);
    }
}
