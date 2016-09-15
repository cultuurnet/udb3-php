<?php

namespace CultuurNet\UDB3\Event\Commands;

class DeleteEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeleteEvent
     */
    protected $deleteEvent;

    public function setUp()
    {
        $this->deleteEvent = new DeleteEvent(
            'id'
        );
    }

    /**
     * @test
     */
    public function it_returns_the_correct_property_values()
    {
        $expectedId = 'id';

        $this->assertEquals($expectedId, $this->deleteEvent->getItemId());
    }
}
