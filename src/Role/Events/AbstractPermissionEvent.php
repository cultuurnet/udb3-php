<?php

namespace CultuurNet\UDB3\Role\Events;

use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;

class AbstractPermissionEvent extends AbstractEvent
{
    /**
     * @var Permission
     */
    private $permission;

    /**
     * AbstractPermissionEvent constructor.
     * @param UUID $uuid
     * @param Permission $permission
     */
    public function __construct(UUID $uuid, Permission $permission)
    {
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

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        return new static(new UUID($data['uuid']), Permission::fromNative($data['permission']));
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'permission' => $this->permission->toNative(),
        );
    }
}
