<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place\ReadModel\JSONLD;

use CultuurNet\UDB3\Place\Events\PlaceProjectedToJSONLD;

class EventFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_factors_an_event_indicating_place_was_projected_to_jsonld()
    {
        $eventFactory = new EventFactory();

        $event = $eventFactory->createEvent('123');

        $this->assertEquals(
            new PlaceProjectedToJSONLD('123'),
            $event
        );
    }
}
