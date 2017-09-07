<?php

namespace CultuurNet\UDB3\Label\Commands;

use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;

class AbstractCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var AbstractCommand
     */
    private $abstractCommand;

    protected function setUp()
    {
        $this->uuid = new UUID();

        $this->abstractCommand = $this->getMockForAbstractClass(
            AbstractCommand::class,
            [$this->uuid]
        );
    }

    /**
     * @test
     */
    public function it_stores_a_uuid()
    {
        $this->assertEquals($this->uuid, $this->abstractCommand->getUuid());
    }

    /**
     * @test
     */
    public function it_has_an_item_id()
    {
        $this->assertEquals(
            $this->uuid->toNative(),
            $this->abstractCommand->getItemId()
        );
    }

    /**
     * @test
     */
    public function it_has_permission_aanbod_labelen()
    {
        $this->assertEquals(
            Permission::AANBOD_LABELEN(),
            $this->abstractCommand->getPermission()
        );
    }
}
