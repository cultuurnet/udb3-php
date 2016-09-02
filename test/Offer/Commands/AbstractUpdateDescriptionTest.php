<?php

namespace CultuurNet\UDB3\Offer\Commands;

class AbstractUpdateDescriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractUpdateDescription|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $updateDescriptionCommand;

    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var string
     */
    protected $description;

    public function setUp()
    {
        $this->itemId = 'Foo';
        $this->description = 'This is the event description update.';

        $this->updateDescriptionCommand = $this->getMockForAbstractClass(
            AbstractUpdateDescription::class,
            array($this->itemId, $this->description)
        );
    }

    /**
     * @test
     */
    public function it_can_return_its_properties()
    {
        $description = $this->updateDescriptionCommand->getDescription();
        $expectedDescription = 'This is the event description update.';

        $this->assertEquals($expectedDescription, $description);

        $itemId = $this->updateDescriptionCommand->getId();
        $expectedItemId = 'Foo';

        $this->assertEquals($expectedItemId, $itemId);
    }
}
