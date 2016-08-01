<?php

namespace CultuurNet\UDB3\Role\Commands;

use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;

class AbstractPermissionCommand extends AbstractCommand
{
    /**
     * @var Permission
     */
    private $permission;

    /**
     * @param UUID $uuid
     * @param Permission $permission
     */
    public function __construct(
        UUID $uuid,
        Permission $permission
    ) {
        parent::__construct($uuid);

        // The built-in serialize call does not work on Enum.
        // Just store them internally as string but expose as Enum.
        $this->permission = $permission->toNative();
    }

    /**
     * @return Permission
     */
    public function getPermission()
    {
        return Permission::fromNative($this->permission);
    }
}
