<?php

namespace CultuurNet\UDB3\Label\Events;

use ValueObjects\Identity\UUID;

abstract class AbstractExtendsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UUID
     */
    protected $uuid;

    /**
     * @var AbstractEvent
     */
    protected $event;

    protected function setUp()
    {
        $this->uuid = new UUID();

        $this->event = $this->createEvent($this->uuid);
    }

    /**
     * @test
     */
    public function it_extends_an_abstract_event()
    {
        $this->assertTrue(is_subclass_of(
            $this->event,
            AbstractEvent::class
        ));
    }

    /**
     * @test
     */
    public function it_can_deserialize()
    {
        $actualEvent = $this->deserialize(
            [AbstractEvent::UUID => $this->uuid->toNative()]
        );

        $this->assertEquals($this->event, $actualEvent);
    }

    /**
     * @param UUID $uuid
     * @return AbstractEvent
     */
    abstract public function createEvent(UUID $uuid);

    /**
     * @param array $array
     * @return AbstractEvent
     */
    abstract public function deserialize(array $array);
}
