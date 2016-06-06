<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\Doctrine\DBAL\SchemaConfiguratorInterface;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use ValueObjects\String\String as StringLiteral;

class SchemaConfigurator implements SchemaConfiguratorInterface
{
    const UUID_COLUMN = 'uuid_col';
    const RELATION_TYPE_COLUMN = 'relationType';
    const RELATION_ID_COLUMN = 'relationId';

    /**
     * @var StringLiteral
     */
    private $tableName;

    /**
     * SchemaConfigurator constructor.
     * @param StringLiteral $tableName
     */
    public function __construct(StringLiteral $tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @param AbstractSchemaManager $schemaManager
     */
    public function configure(AbstractSchemaManager $schemaManager)
    {
        $schema = $schemaManager->createSchema();

        if (!$schema->hasTable($this->tableName->toNative())) {
            $table = $this->createTable($schema, $this->tableName);

            $schemaManager->createTable($table);
        }
    }

    /**
     * @param Schema $schema
     * @param StringLiteral $tableName
     * @return \Doctrine\DBAL\Schema\Table
     */
    private function createTable(Schema $schema, StringLiteral $tableName)
    {
        $table = $schema->createTable($tableName->toNative());

        $table->addColumn(self::UUID_COLUMN, Type::GUID)
            ->setLength(36)
            ->setNotnull(true);

        $table->addColumn(self::RELATION_TYPE_COLUMN, Type::STRING)
            ->setLength(255)
            ->setNotnull(true);

        $table->addColumn(self::RELATION_ID_COLUMN, Type::GUID)
            ->setLength(36)
            ->setNotnull(true);

        $table->addIndex([self::UUID_COLUMN]);
        $table->addUniqueIndex([
            self::UUID_COLUMN,
            self::RELATION_TYPE_COLUMN,
            self::RELATION_ID_COLUMN
        ]);

        return $table;
    }
}
