<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Role\ValueObjects\Permission;

class AbstractCommand implements AuthorizableCommandInterface
{
    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var Permission[]
     */
    protected $permissions;

    /**
     * AbstractCommand constructor.
     * @param $itemId
     */
    public function __construct($itemId)
    {
        $this->itemId = $itemId;
        $this->permissions = [];
    }

    /**
     * @return string
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @return Permission[]
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}
