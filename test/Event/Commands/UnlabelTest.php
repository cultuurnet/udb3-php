<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Label;

class UnlabelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UnLabel
     */
    protected $unLabel;

    public function setUp()
    {
        $this->unLabel = new Unlabel(
            'id',
            new Label('testlabel')
        );
    }

    /**
     * @test
     */
    public function it_returns_the_correct_property_values()
    {
        $expectedId = 'id';
        $expectedLabel = new Label('testlabel');

        $this->assertEquals($expectedId, $this->unLabel->getEventId());
        $this->assertEquals($expectedLabel, $this->unLabel->getLabel());
    }
}
