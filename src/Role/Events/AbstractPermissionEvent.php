<?php

namespace CultuurNet\UDB3\Role\Events;

use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;

abstract class AbstractPermissionEvent extends AbstractEvent
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
    final public function __construct(UUID $uuid, Permission $permission)
    {
        parent::__construct($uuid);
        $this->permission = $permission;
    }

    /**
     * @return Permission
     */
    public function getPermission(): Permission
    {
        return $this->permission;
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data): AbstractPermissionEvent
    {
        return new static(new UUID($data['uuid']), Permission::fromNative($data['permission']));
    }

    /**
     * @inheritdoc
     */
    public function serialize(): array
    {
        return parent::serialize() + array(
            'permission' => $this->permission->toNative(),
        );
    }
}
