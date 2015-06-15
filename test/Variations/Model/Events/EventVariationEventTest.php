<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Model\Events;

use CultuurNet\UDB3\Variations\Model\Properties\Id;

class EventVariationEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_supports_serialization()
    {
        /** @var EventVariationEvent $event */
        $event = $this->getMockForAbstractClass(
            EventVariationEvent::class,
            [
                new Id('28CD91A1-EB0F-49A9-991D-5DCAFEC0A043')
            ]
        );

        $serialized = $event->serialize();

        $this->assertEquals(
            [
                'id' => '28CD91A1-EB0F-49A9-991D-5DCAFEC0A043',
            ],
            $serialized
        );

        $this->assertEquals(
            $event,
            $event::deserialize($serialized)
        );
    }
}
