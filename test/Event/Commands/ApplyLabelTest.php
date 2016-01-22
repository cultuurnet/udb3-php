<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Label;

class ApplyLabelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApplyLabel
     */
    protected $applyLabel;

    /**
     * @var Label
     */
    protected $label;

    public function setUp()
    {
        $this->label = new Label('test label');
        $this->applyLabel = new ApplyLabel(
            'id',
            $this->label
        );
    }

    /**
     * @test
     */
    public function it_returns_the_correct_property_values()
    {
        $expectedId = 'id';
        $expectedLabel = new Label('test label');

        $this->assertEquals($expectedId, $this->applyLabel->getEventId());
        $this->assertEquals($expectedLabel, $this->applyLabel->getLabel());
    }
}
