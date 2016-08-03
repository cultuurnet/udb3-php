<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\DBALTestConnectionTrait;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\OfferLabelRelation;
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
     * @param OfferLabelRelation $offerLabelRelation
     */
    protected function saveOfferLabelRelation(OfferLabelRelation $offerLabelRelation)
    {
        $values = $this->offerLabelRelationToValues($offerLabelRelation);

        $sql = 'INSERT INTO ' . $this->tableName . ' VALUES (?, ?, ?)';

        $this->connection->executeQuery($sql, $values);
    }

    /**
     * @param OfferLabelRelation $offerLabelRelation
     * @return array
     */
    protected function offerLabelRelationToValues(OfferLabelRelation $offerLabelRelation)
    {
        return [
            $offerLabelRelation->getUuid()->toNative(),
            $offerLabelRelation->getOfferType()->toNative(),
            $offerLabelRelation->getOfferId()
        ];
    }

    /**
     * @return OfferLabelRelation
     */
    protected function getLastOfferLabelRelation()
    {
        $sql = 'SELECT * FROM ' . $this->tableName;

        $statement = $this->connection->executeQuery($sql);
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $rows ? OfferLabelRelation::fromRelationalData($rows[count($rows) - 1]) : null;
    }
}
