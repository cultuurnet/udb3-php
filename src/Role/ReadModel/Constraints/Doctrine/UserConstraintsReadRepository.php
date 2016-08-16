<?php

namespace CultuurNet\UDB3\Role\ReadModel\Constraints\Doctrine;

use CultuurNet\UDB3\Role\ReadModel\Constraints\UserConstraintsReadRepositoryInterface;
use CultuurNet\UDB3\Role\ReadModel\Permissions\Doctrine\SchemaConfigurator as PermissionsSchemaConfigurator;
use CultuurNet\UDB3\Role\ReadModel\Constraints\Doctrine\SchemaConfigurator as ConstraintsSchemaConfigurator;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use Doctrine\DBAL\Connection;
use ValueObjects\String\String as StringLiteral;

class UserConstraintsReadRepository implements UserConstraintsReadRepositoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var StringLiteral
     */
    private $userRolesTableName;

    /**
     * @var StringLiteral
     */
    private $rolePermissionsTableName;

    /**
     * @var StringLiteral
     */
    private $roleConstraintTableName;

    /**
     * UserConstraintsReadRepository constructor.
     * @param Connection $connection
     * @param StringLiteral $userRolesTableName
     * @param StringLiteral $rolePermissionsTableName
     * @param StringLiteral $roleConstraintTableName
     */
    public function __construct(
        Connection $connection,
        StringLiteral $userRolesTableName,
        StringLiteral $rolePermissionsTableName,
        StringLiteral $roleConstraintTableName
    ) {
        $this->connection = $connection;
        $this->userRolesTableName = $userRolesTableName;
        $this->rolePermissionsTableName = $rolePermissionsTableName;
        $this->roleConstraintTableName = $roleConstraintTableName;
    }

    /**
     * @param StringLiteral $userId
     * @param Permission $permission
     * @return StringLiteral[]
     */
    public function getByUserAndPermission(
        StringLiteral $userId,
        Permission $permission
    ) {
        $userRolesSubQuery = $this->connection->createQueryBuilder()
            ->select(PermissionsSchemaConfigurator::ROLE_ID_COLUMN)
            ->from($this->userRolesTableName->toNative())
            ->where(PermissionsSchemaConfigurator::USER_ID_COLUMN . ' = :userId');

        $userConstraintsQuery = $this->connection->createQueryBuilder()
            ->select('rc.' . ConstraintsSchemaConfigurator::CONSTRAINT_COLUMN)
            ->from($this->roleConstraintTableName, 'rc')
            ->innerJoin(
                'rc',
                sprintf('(%s)', $userRolesSubQuery->getSQL()),
                'ur',
                'rc.' . PermissionsSchemaConfigurator::ROLE_ID_COLUMN . ' = ur.' . PermissionsSchemaConfigurator::ROLE_ID_COLUMN
            )
            ->innerJoin(
                'rc',
                $this->rolePermissionsTableName->toNative(),
                'rp',
                'rc.' . PermissionsSchemaConfigurator::ROLE_ID_COLUMN . ' = rp.' . PermissionsSchemaConfigurator::ROLE_ID_COLUMN
            )
            ->where(PermissionsSchemaConfigurator::PERMISSION_COLUMN . ' = :permission')
            ->setParameter('userId', $userId->toNative())
            ->setParameter('permission', $permission->toNative());

        $results = $userConstraintsQuery->execute()->fetchAll(\PDO::FETCH_COLUMN);

        return array_map(function ($constraint) {
            return new StringLiteral($constraint);
        }, $results);
    }
}
