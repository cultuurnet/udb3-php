<?php

namespace CultuurNet\UDB3\Label\Events;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

abstract class AbstractExtendsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UUID
     */
    protected $uuid;

    /**
     * @var StringLiteral
     */
    protected $name;

    /**
     * @var AbstractEvent
     */
    protected $event;

    protected function setUp()
    {
        $this->uuid = new UUID();

        $this->name = new StringLiteral('2dotstwice');

        $this->event = $this->createEvent($this->uuid, $this->name);
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
            [
                'uuid' => $this->uuid->toNative(),
                'name' => $this->name->toNative(),
            ]
        );

        $this->assertEquals($this->event, $actualEvent);
    }

    /**
     * @param UUID $uuid
     * @param StringLiteral $name
     * @return AbstractEvent
     */
    abstract public function createEvent(UUID $uuid, StringLiteral $name);

    /**
     * @param array $array
     * @return AbstractEvent
     */
    abstract public function deserialize(array $array);
}
