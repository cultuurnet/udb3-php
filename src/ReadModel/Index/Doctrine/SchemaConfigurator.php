<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\ReadModel\Index\Doctrine;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use ValueObjects\String\String as StringLiteral;

class SchemaConfigurator
{
    /**
     * @var StringLiteral
     */
    protected $tableName;

    public function __construct(StringLiteral $tableName)
    {
        $this->tableName = $tableName;
    }

    public function configure(AbstractSchemaManager $schemaManager)
    {
        $schema = $schemaManager->createSchema();
        $table = $schema->createTable($this->tableName->toNative());

        $table->addColumn(
            'entity_id',
            'string',
            array('length' => 36, 'notnull' => true)
        );
        $table->addColumn(
            'entity_type',
            'string',
            array('length' => 36, 'notnull' => true)
        );
        $table->addColumn(
            'title',
            'string',
            array('length' => 36, 'notnull' => true)
        );
        $table->addColumn(
            'uid',
            'string',
            array('length' => 36, 'notnull' => true)
        );
        $table->addColumn(
            'zip',
            'string',
            array('length' => 36, 'notnull' => false)
        );
        $table->addColumn(
            'created',
            'string',
            array('length' => 36, 'notnull' => true)
        );

        $table->setPrimaryKey(['entity_id', 'entity_type']);

        $schemaManager->createTable($table);
    }
}
