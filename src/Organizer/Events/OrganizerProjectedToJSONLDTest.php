<?php

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\Organizer\OrganizerProjectedToJSONLD;

class OrganizerProjectedToJSONLDTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_serialized_and_deserialized()
    {
        $event = new OrganizerProjectedToJSONLD(
            '064469b2-ee5d-4987-86af-fedc822b1a32',
            'organizers/064469b2-ee5d-4987-86af-fedc822b1a32'
        );

        $serialized = $event->serialize();
        $deserialized = OrganizerProjectedToJSONLD::deserialize($serialized);

        $this->assertTrue(is_array($serialized));
        $this->assertEquals($event, $deserialized);
    }
}
