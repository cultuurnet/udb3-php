<?php

namespace CultuurNet\UDB3\Role\Events;

use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

class AbstractConstraintEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UUID
     */
    protected $uuid;

    /**
     * @var StringLiteral
     */
    protected $query;

    /**
     * @var AbstractConstraintEvent
     */
    protected $event;

    protected function setUp()
    {
        $this->uuid = new UUID();

        $this->query = new StringLiteral('category_flandersregion_name:"Regio Aalst"');

        $this->event = $this->getMockForAbstractClass(
            AbstractConstraintEvent::class,
            [$this->uuid, $this->query]
        );
    }

    /**
     * @test
     */
    public function it_stores_a_uuid_and_a_query()
    {
        $this->assertEquals($this->uuid, $this->event->getUuid());
        $this->assertEquals($this->query, $this->event->getQuery());
    }

    /**
     * @test
     */
    public function it_can_serialize()
    {
        $actualArray = $this->event->serialize();

        $expectedArray = [
            'uuid' => $this->uuid->toNative(),
            'query' => $this->query->toNative()
        ];

        $this->assertEquals($expectedArray, $actualArray);
    }

    public function it_can_deserialize()
    {
        $data = [
            'uuid' => $this->uuid->toNative(),
            'query' => $this->query->toNative()
        ];
        $actualEvent = $this->event->deserialize($data);
        $expectedEvent = $this->event;

        $this->assertEquals($actualEvent, $expectedEvent);
    }
}
