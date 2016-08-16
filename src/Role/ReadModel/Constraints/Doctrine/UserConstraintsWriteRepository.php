<?php

namespace CultuurNet\UDB3\Role\ReadModel\Constraints\Doctrine;

use CultuurNet\UDB3\Role\ReadModel\Constraints\UserConstraintsWriteRepositoryInterface;
use Doctrine\DBAL\Connection;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class UserConstraintsWriteRepository implements UserConstraintsWriteRepositoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var StringLiteral
     */
    private $roleConstraintTableName;

    /**
     * UserConstraintWriteRepository constructor.
     * @param Connection $connection
     * @param StringLiteral $roleConstraintTableName
     */
    public function __construct(
        Connection $connection,
        StringLiteral $roleConstraintTableName
    ) {
        $this->connection = $connection;
        $this->roleConstraintTableName = $roleConstraintTableName;
    }

    /**
     * @inheritdoc
     */
    public function removeRole(UUID $roleId)
    {
        $this->connection->delete(
            $this->roleConstraintTableName,
            [SchemaConfigurator::ROLE_ID_COLUMN => $roleId->toNative()]
        );
    }

    /**
     * @inheritdoc
     */
    public function insertRole(UUID $roleId, StringLiteral $constraint)
    {
        $this->connection->insert(
            $this->roleConstraintTableName,
            [
                SchemaConfigurator::ROLE_ID_COLUMN => $roleId->toNative(),
                SchemaConfigurator::CONSTRAINT_COLUMN => $constraint->toNative()
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function updateRole(UUID $roleId, StringLiteral $constraint)
    {
        $this->connection->update(
            $this->roleConstraintTableName,
            [
                SchemaConfigurator::ROLE_ID_COLUMN => $roleId->toNative(),
                SchemaConfigurator::CONSTRAINT_COLUMN => $constraint->toNative()
            ],
            [SchemaConfigurator::ROLE_ID_COLUMN => $roleId->toNative()]
        );
    }
}
