<?php

namespace CultuurNet\UDB3\Role\ReadModel\Permissions\Doctrine;

use CultuurNet\UDB3\Role\ReadModel\Permissions\UserPermissionsWriteRepositoryInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use Doctrine\DBAL\Connection;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class UserPermissionsWriteRepository implements UserPermissionsWriteRepositoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var StringLiteral
     */
    private $userRoleTableName;

    /**
     * @var StringLiteral
     */
    private $rolePermissionTableName;

    /**
     * UserPermissionsWriteRepository constructor.
     * @param Connection $connection
     * @param StringLiteral $userRoleTableName
     * @param StringLiteral $rolePermissionTableName
     */
    public function __construct(
        Connection $connection,
        StringLiteral $userRoleTableName,
        StringLiteral $rolePermissionTableName
    ) {
        $this->connection = $connection;
        $this->userRoleTableName = $userRoleTableName;
        $this->rolePermissionTableName = $rolePermissionTableName;
    }

    public function removePermissionsByRole(UUID $roleId)
    {
        $connection = $this->connection;

        $connection->delete(
            $this->userRoleTableName,
            array(SchemaConfigurator::ROLE_ID_COLUMN => (string) $roleId)
        );

        $connection->delete(
            $this->rolePermissionTableName,
            array(SchemaConfigurator::ROLE_ID_COLUMN => (string) $roleId)
        );
    }

    public function addRolePermission(UUID $roleId, Permission $permission)
    {
        $this->connection->insert(
            $this->rolePermissionTableName,
            array(
                SchemaConfigurator::ROLE_ID_COLUMN => (string) $roleId,
                SchemaConfigurator::PERMISSION_COLUMN => (string) $permission
            )
        );
    }

    public function removeRolePermission(UUID $roleId, Permission $permission)
    {
        $this->connection->delete(
            $this->rolePermissionTableName,
            array(
                SchemaConfigurator::ROLE_ID_COLUMN => (string) $roleId,
                SchemaConfigurator::PERMISSION_COLUMN => (string) $permission
            )
        );
    }

    public function addPermissionsByUserRole($userId, UUID $roleId)
    {
        $this->connection->insert(
            $this->userRoleTableName,
            array(
                SchemaConfigurator::ROLE_ID_COLUMN => (string) $roleId,
                SchemaConfigurator::USER_ID_COLUMN => $userId
            )
        );
    }

    public function removePermissionsByUserRole($userId, UUID $roleId)
    {
        $this->connection->delete(
            $this->userRoleTableName,
            array(
                SchemaConfigurator::USER_ID_COLUMN => $userId,
                SchemaConfigurator::ROLE_ID_COLUMN => (string) $roleId
            )
        );
    }
}
