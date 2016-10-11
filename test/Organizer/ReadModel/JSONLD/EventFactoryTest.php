<?php

namespace CultuurNet\UDB3\Organizer\ReadModel\JSONLD;

use CultuurNet\UDB3\Organizer\OrganizerProjectedToJSONLD;

class EventFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new EventFactory();
    }

    /**
     * @test
     */
    public function it_creates_an_organizer_projected_to_json_ld_event_with_the_organizer_id()
    {
        $id = '0be365fb-d897-410d-81e5-b1bdcad63639';
        $expectedEvent = new OrganizerProjectedToJSONLD($id);

        $actualEvent = $this->factory->createEvent($id);

        $this->assertEquals($expectedEvent, $actualEvent);
    }
}
