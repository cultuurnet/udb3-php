<?php

namespace CultuurNet\UDB3\Role\ReadModel\Constraints\Doctrine;

use CultuurNet\UDB3\Role\ReadModel\Constraints\UserConstraintsWriteRepositoryInterface;
use Doctrine\DBAL\Connection;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class UserConstraintWriteRepository implements UserConstraintsWriteRepositoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var StringLiteral
     */
    private $roleConstraintsTableName;

    /**
     * UserConstraintWriteRepository constructor.
     * @param Connection $connection
     * @param StringLiteral $roleConstraintsTableName
     */
    public function __construct(
        Connection $connection,
        StringLiteral $roleConstraintsTableName
    ) {
        $this->connection = $connection;
        $this->roleConstraintsTableName = $roleConstraintsTableName;
    }

    /**
     * @inheritdoc
     */
    public function removeRole(UUID $roleId)
    {
        $this->connection->delete(
            $this->roleConstraintsTableName,
            [SchemaConfigurator::ROLE_ID_COLUMN => $roleId->toNative()]
        );
    }

    /**
     * @inheritdoc
     */
    public function insertRole(UUID $roleId, StringLiteral $constraint)
    {
        $this->connection->insert(
            $this->roleConstraintsTableName,
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
            $this->roleConstraintsTableName,
            [
                SchemaConfigurator::ROLE_ID_COLUMN => $roleId->toNative(),
                SchemaConfigurator::CONSTRAINT_COLUMN => $constraint->toNative()
            ],
            [SchemaConfigurator::ROLE_ID_COLUMN => $roleId->toNative()]
        );
    }
}
