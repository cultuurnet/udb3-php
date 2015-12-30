<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\Entry\Keyword;

class LabelEventsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LabelEvents
     */
    protected $labelEvents;

    public function setUp()
    {
        $this->labelEvents = new LabelEvents(
            array('id1', 'id2', 'id3'),
            new Keyword('testlabel')
        );
    }

    /**
     * @test
     */
    public function it_returns_the_correct_property_values()
    {
        $expectedIds = array('id1', 'id2', 'id3');
        $expectedKeyword = new Keyword('testlabel');

        $this->assertEquals($expectedIds, $this->labelEvents->getEventIds());
        $this->assertEquals($expectedKeyword, $this->labelEvents->getLabel());
    }
}
