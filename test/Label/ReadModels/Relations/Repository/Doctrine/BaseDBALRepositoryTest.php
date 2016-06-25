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
     * @param OfferLabelRelation $entity
     */
    protected function saveEntity(OfferLabelRelation $entity)
    {
        $values = $this->entityToValues($entity);

        $sql = 'INSERT INTO ' . $this->tableName . ' VALUES (?, ?, ?, ?)';

        $this->connection->executeQuery($sql, $values);
    }

    /**
     * @param OfferLabelRelation $entity
     * @return array
     */
    protected function entityToValues(OfferLabelRelation $entity)
    {
        return [
            $entity->getUuid()->toNative(),
            $entity->getLabelName(),
            $entity->getRelationType()->toNative(),
            $entity->getRelationId()
        ];
    }

    /**
     * @return OfferLabelRelation
     */
    protected function getLastEntity()
    {
        $sql = 'SELECT * FROM ' . $this->tableName;

        $statement = $this->connection->executeQuery($sql);
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $rows ? OfferLabelRelation::fromRelationalData($rows[count($rows) - 1]) : null;
    }
}
