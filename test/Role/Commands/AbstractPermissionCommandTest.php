<?php

namespace CultuurNet\UDB3\Role\Commands;

use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;

class AbstractPermissionCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var Permission
     */
    private $permission;

    /**
     * @var AbstractPermissionCommand
     */
    private $abstractPermissionCommand;

    protected function setUp()
    {
        $this->uuid = new UUID();
        $this->permission =Permission::AANBOD_BEWERKEN();

        $this->abstractPermissionCommand = $this->getMockForAbstractClass(
            AbstractPermissionCommand::class,
            [$this->uuid, $this->permission]
        );
    }

    /**
     * @test
     */
    public function it_stores_a_permission()
    {
        $this->assertEquals($this->permission, $this->abstractPermissionCommand->getPermission());
    }
}
