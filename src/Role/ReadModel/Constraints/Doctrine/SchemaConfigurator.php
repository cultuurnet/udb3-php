<?php

namespace CultuurNet\UDB3\Role\ReadModel\Constraints\Doctrine;

use CultuurNet\UDB3\Doctrine\DBAL\SchemaConfiguratorInterface;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Types\Type;
use ValueObjects\String\String as StringLiteral;

class SchemaConfigurator implements SchemaConfiguratorInterface
{
    const ROLE_ID_COLUMN = 'role_id';
    const CONSTRAINT_COLUMN = 'constraint_col';

    /**
     * @var StringLiteral
     */
    private $roleConstraintTableName;

    /**
     * SchemaConfigurator constructor.
     * @param StringLiteral $roleConstraintTableName
     */
    public function __construct(StringLiteral $roleConstraintTableName)
    {
        $this->roleConstraintTableName = $roleConstraintTableName;
    }

    /**
     * @param AbstractSchemaManager $schemaManager
     */
    public function configure(AbstractSchemaManager $schemaManager)
    {
        $schema = $schemaManager->createSchema();

        if (!$schema->hasTable((string) $this->roleConstraintTableName)) {
            $roleConstraintTable = $schema->createTable(
                $this->roleConstraintTableName->toNative()
            );

            $roleConstraintTable->addColumn(self::ROLE_ID_COLUMN, Type::GUID)
                ->setLength(36)
                ->setNotnull(true);

            $roleConstraintTable->addColumn(self::CONSTRAINT_COLUMN, Type::STRING)
                ->setLength(255)
                ->setNotnull(true);

            $roleConstraintTable->setPrimaryKey([self::ROLE_ID_COLUMN]);

            $schemaManager->createTable($roleConstraintTable);
        }
    }
}
