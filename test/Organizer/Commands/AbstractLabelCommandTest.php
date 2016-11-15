<?php

namespace CultuurNet\UDB3\Organizer\Commands;

use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;

class AbstractLabelCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $organizerId;

    /**
     * @var UUID
     */
    private $labelId;

    /**
     * @var AbstractLabelCommand
     */
    private $abstractLabelCommand;

    protected function setUp()
    {
        $this->organizerId = 'organizerId';

        $this->labelId = new UUID();

        $this->abstractLabelCommand = $this->getMockForAbstractClass(
            AbstractLabelCommand::class,
            [$this->organizerId, $this->labelId]
        );
    }

    /**
     * @test
     */
    public function it_stores_an_organizer_id()
    {
        $this->assertEquals(
            $this->organizerId,
            $this->abstractLabelCommand->getOrganizerId()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_label_id()
    {
        $this->assertEquals(
            $this->labelId,
            $this->abstractLabelCommand->getLabelId()
        );
    }

    /**
     * @test
     */
    public function it_returns_an_item_id()
    {
        $this->assertEquals(
            $this->organizerId,
            $this->abstractLabelCommand->getItemId()
        );
    }

    /**
     * @test
     */
    public function it_returns_a_permission()
    {
        $this->assertEquals(
            Permission::AANBOD_BEWERKEN(),
            $this->abstractLabelCommand->getPermission()
        );
    }

    /**
     * @test
     */
    public function it_is_identified_by_uuid()
    {
        $this->assertTrue($this->abstractLabelCommand->isIdentifiedByUuid());
    }

    /**
     * @test
     */
    public function it_does_not_use_label_name()
    {
        $this->assertNull($this->abstractLabelCommand->getName());
    }

    /**
     * @test
     */
    public function it_does_use_label_uuid()
    {
        $this->assertEquals(
            $this->labelId,
            $this->abstractLabelCommand->getUuid()
        );
    }

    /**
     * @test
     */
    public function it_is_not_always_allowed()
    {
        $this->assertFalse($this->abstractLabelCommand->isAlwaysAllowed());
    }

    /**
     * @test
     */
    public function it_can_be_made_always_allowed()
    {
        $labelCommand = $this->abstractLabelCommand->withAlwaysAllowed(true);

        $this->assertTrue($labelCommand->isAlwaysAllowed());
    }
}
