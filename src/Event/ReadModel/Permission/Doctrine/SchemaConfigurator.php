<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\Permission\Doctrine;

use CultuurNet\UDB3\Doctrine\DBAL\SchemaConfiguratorInterface;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use ValueObjects\String\String as StringLiteral;

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
            'event_id',
            'guid',
            array('length' => 36, 'notnull' => true)
        );
        $table->addColumn(
            'user_id',
            'guid',
            array('length' => 36, 'notnull' => true)
        );

        $table->setPrimaryKey(['event_id', 'user_id']);

        $schemaManager->createTable($table);
    }
}
