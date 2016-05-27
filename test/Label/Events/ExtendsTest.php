<?php

namespace CultuurNet\UDB3\Label\Events;

use ValueObjects\Identity\UUID;

abstract class ExtendsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UUID
     */
    protected $uuid;

    /**
     * @var Event
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
            Event::class
        ));
    }

    /**
     * @param UUID $uuid
     * @return Event
     */
    abstract public function createEvent(UUID $uuid);
}
