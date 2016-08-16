<?php

namespace CultuurNet\UDB3\Role\ReadModel\Constraints\Doctrine;

use CultuurNet\UDB3\Doctrine\DBAL\SchemaConfiguratorInterface;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Types\Type;
use ValueObjects\String\String as StringLiteral;

class SchemaConfigurator implements SchemaConfiguratorInterface
{
    const ROLE_ID_COLUMN = 'role_id';
    const CONSTRAINT_COLUMN = 'permission';

    /**
     * @var StringLiteral
     */
    private $userConstraintsTableName;

    /**
     * SchemaConfigurator constructor.
     * @param StringLiteral $userConstraintsTableName
     */
    public function __construct(StringLiteral $userConstraintsTableName)
    {
        $this->userConstraintsTableName = $userConstraintsTableName;
    }

    /**
     * @param AbstractSchemaManager $schemaManager
     */
    public function configure(AbstractSchemaManager $schemaManager)
    {
        $schema = $schemaManager->createSchema();

        if (!$schema->hasTable((string) $this->userConstraintsTableName)) {
            $roleConstraintsTable = $schema->createTable(
                $this->userConstraintsTableName->toNative()
            );

            $roleConstraintsTable->addColumn(self::ROLE_ID_COLUMN, Type::GUID)
                ->setLength(36)
                ->setNotnull(true);

            $roleConstraintsTable->addColumn(self::CONSTRAINT_COLUMN, Type::STRING)
                ->setLength(255)
                ->setNotnull(true);

            $roleConstraintsTable->setPrimaryKey([self::ROLE_ID_COLUMN]);

            $schemaManager->createTable($roleConstraintsTable);
        }
    }
}
