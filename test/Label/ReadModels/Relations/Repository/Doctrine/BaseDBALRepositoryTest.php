<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\DBALTestConnectionTrait;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\LabelRelation;
use ValueObjects\String\String as StringLiteral;

abstract class BaseDBALRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use DBALTestConnectionTrait;

    /**
     * @var StringLiteral
     */
    private $tableName;

    protected function setUp()
    {
        $this->tableName = new StringLiteral('test_places_relations');

        $schemaConfigurator = new SchemaConfigurator($this->tableName);

        $schemaManager = $this->getConnection()->getSchemaManager();

        $schemaConfigurator->configure($schemaManager);
    }

    /**
     * @return StringLiteral
     */
    protected function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param LabelRelation $offerLabelRelation
     */
    protected function saveOfferLabelRelation(LabelRelation $offerLabelRelation)
    {
        $values = $this->offerLabelRelationToValues($offerLabelRelation);

        $sql = 'INSERT INTO ' . $this->tableName . ' VALUES (?, ?, ?)';

        $this->connection->executeQuery($sql, $values);
    }

    /**
     * @param LabelRelation $offerLabelRelation
     * @return array
     */
    protected function offerLabelRelationToValues(LabelRelation $offerLabelRelation)
    {
        return [
            $offerLabelRelation->getUuid()->toNative(),
            $offerLabelRelation->getRelationType()->toNative(),
            $offerLabelRelation->getRelationId()
        ];
    }

    /**
     * @return LabelRelation
     */
    protected function getLastOfferLabelRelation()
    {
        $sql = 'SELECT * FROM ' . $this->tableName;

        $statement = $this->connection->executeQuery($sql);
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $rows ? LabelRelation::fromRelationalData($rows[count($rows) - 1]) : null;
    }
}
