<?php

namespace CultuurNet\UDB3\Offer\Item\Events;

use CultuurNet\UDB3\Theme;

class ThemeUpdatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_be_serializable()
    {
        $event = new ThemeUpdated(
            '9B70683A-5ABF-4A21-80CE-D3A1C0C7BCC2',
            new Theme('0.52.0.0.0', 'Circus')
        );

        $eventData = $event->serialize();
        $deserializedEvent = ThemeUpdated::deserialize($eventData);

        $this->assertEquals($event, $deserializedEvent);
    }
}
