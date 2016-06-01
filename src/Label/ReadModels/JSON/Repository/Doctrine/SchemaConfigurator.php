<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Doctrine;

use CultuurNet\UDB3\Doctrine\DBAL\SchemaConfiguratorInterface;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use ValueObjects\String\String as StringLiteral;

class SchemaConfigurator implements SchemaConfiguratorInterface
{
    const UUID_COLUMN = 'uuid_col';
    const NAME_COLUMN = 'name';
    const VISIBLE_COLUMN = 'visible';
    const PRIVATE_COLUMN = 'private';
    const PARENT_UUID_COLUMN = 'parentUuid';
    const COUNT_COLUMN = 'count_col';

    /**
     * @var StringLiteral
     */
    protected $tableName;

    /**
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

    private function createTable(Schema $schema, StringLiteral $tableName)
    {
        $table = $schema->createTable($tableName->toNative());

        $table->addColumn(self::UUID_COLUMN, Type::GUID)
            ->setLength(36)
            ->setNotnull(true);

        $table->addColumn(self::NAME_COLUMN, Type::STRING)
            ->setLength(256)
            ->setNotnull(true);

        $table->addColumn(self::VISIBLE_COLUMN, Type::BOOLEAN)
            ->setNotnull(true)
            ->setDefault(true);

        $table->addColumn(self::PRIVATE_COLUMN, Type::BOOLEAN)
            ->setNotnull(true)
            ->setDefault(false);

        $table->addColumn(self::PARENT_UUID_COLUMN, Type::GUID)
            ->setLength(36)
            ->setNotnull(false);

        $table->addColumn(self::COUNT_COLUMN, Type::BIGINT)
            ->setNotnull(true)
            ->setDefault(0);

        $table->setPrimaryKey([self::UUID_COLUMN]);
        $table->addUniqueIndex([self::UUID_COLUMN, self::NAME_COLUMN]);

        return $table;
    }
}
