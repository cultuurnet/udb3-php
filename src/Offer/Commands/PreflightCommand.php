<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Role\ValueObjects\Permission;

class PreflightCommand extends AbstractCommand
{
    /**
     * @var Permission
     */
    private static $permission;

    /**
     * @param string $itemId
     * @param Permission $permission
     */
    public function __construct($itemId, $permission)
    {
        parent::__construct($itemId);
        self::$permission = $permission;
    }

    public static function getPermission()
    {
        return self::$permission;
    }
}
