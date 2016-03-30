<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\ReadModel\Index\Doctrine;

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
            'entity_id',
            'guid',
            array('length' => 36, 'notnull' => true)
        );
        $table->addColumn(
            'entity_type',
            'string',
            array('length' => 36, 'notnull' => true)
        );
        $table->addColumn(
            'title',
            'text'
        );
        $table->addColumn(
            'uid',
            'guid',
            array('length' => 36, 'notnull' => true)
        );
        $table->addColumn(
            'zip',
            'text'
        );
        $table->addColumn(
            'created',
            'text',
            array('length' => 36, 'notnull' => true)
        );
        $table->addColumn(
            'updated',
            'text',
            array('length' => 36, 'notnull' => true)
        );
        $table->addColumn(
            'owning_domain',
            'text',
            array('length' => 36, 'notnull' => true)
        );
        $table->addColumn(
            'entity_iri',
            'text'
        );

        $schemaManager->createTable($table);
    }
}
