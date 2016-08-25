<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Role\ValueObjects\Permission;

class PreflightCommand extends AbstractCommand
{
    /**
     * PreflightCommand constructor.
     * @param string $itemId
     * @param Permission[] $permissions
     */
    public function __construct($itemId, $permissions)
    {
        parent::__construct($itemId);
        $this->permissions = $permissions;
    }
}
