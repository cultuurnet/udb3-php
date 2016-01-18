<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Label;

class AbstractAddLabelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MockAddLabel
     */
    protected $labelCommand;

    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var Label
     */
    protected $label;

    public function setUp()
    {
        $this->itemId = 'Foo';
        $this->label = new Label('LabelTest');
        $this->labelCommand = new MockAddLabel($this->itemId, $this->label);
    }

    /**
     * @test
     */
    public function it_can_be_instantiated_with_properties()
    {
        $expectedItemId = 'Foo';
        $expectedLabel = new Label('LabelTest');
        $expectedLabelCommand = new MockAddLabel($expectedItemId, $expectedLabel);

        $this->assertEquals($expectedLabelCommand, $this->labelCommand);
    }
}
