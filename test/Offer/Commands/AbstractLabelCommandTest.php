<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Role\ValueObjects\Permission;

class AbstractLabelCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractLabelCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $labelCommand;

    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var Permission[]
     */
    protected $permissions;

    /**
     * @var Label
     */
    protected $label;

    public function setUp()
    {
        $this->itemId = 'Foo';
        $this->label = new Label('LabelTest');

        $this->labelCommand = $this->getMockForAbstractClass(
            AbstractLabelCommand::class,
            array($this->itemId, $this->label)
        );
    }

    /**
     * @test
     */
    public function it_can_return_its_properties()
    {
        $label = $this->labelCommand->getLabel();
        $expectedLabel = new Label('LabelTest');

        $this->assertEquals($expectedLabel, $label);

        $itemId = $this->labelCommand->getItemId();
        $expectedItemId = 'Foo';

        $this->assertEquals($expectedItemId, $itemId);

        $permissions = $this->labelCommand->getPermissions();
        $expectedPermissions = [
            Permission::AANBOD_BEWERKEN(),
        ];

        $this->assertEquals($expectedPermissions, $permissions);
    }
}
