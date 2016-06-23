<?php

namespace CultuurNet\UDB3\Role\Commands;

use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;

class AddPermission extends AbstractCommand
{
    /**
     * @var Permission
     */
    private $permission;

    /**
     * AddPermission constructor.
     * @param UUID $uuid
     * @param Permission $permission
     */
    public function __construct(
        UUID $uuid,
        Permission $permission
    ) {
        parent::__construct($uuid);

        $this->permission = $permission;
    }

    /**
     * @return Permission
     */
    public function getPermission()
    {
        return $this->permission;
    }
}
