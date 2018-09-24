<?php

namespace CultuurNet\UDB3\MyOrganizers\ReadModel\Doctrine;

use CultuurNet\UDB3\Doctrine\DBAL\SchemaConfiguratorInterface;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use ValueObjects\StringLiteral\StringLiteral;

class SchemaConfigurator implements SchemaConfiguratorInterface
{
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
     * @inheritdoc
     */
    public function configure(AbstractSchemaManager $schemaManager)
    {
        $schema = $schemaManager->createSchema();
        $table = $schema->createTable($this->tableName->toNative());

        $table->addColumn(
            'id',
            'guid',
            array('length' => 36, 'notnull' => true)
        );
        $table->addColumn(
            'uid',
            'guid',
            array('length' => 36, 'notnull' => true)
        );
        $table->addColumn(
            'created',
            'string',
            array('length' => 32, 'notnull' => true)
        );
        $table->addColumn(
            'updated',
            'string',
            array('length' => 32, 'notnull' => true)
        );

        $table->setPrimaryKey(['id']);

        $table->addIndex(['uid']);

        $schemaManager->createTable($table);
    }
}
