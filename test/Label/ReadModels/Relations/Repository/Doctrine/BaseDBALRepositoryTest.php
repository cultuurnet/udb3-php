<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\DBALTestConnectionTrait;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Entity;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use ValueObjects\Identity\UUID;
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
     * @param Entity $entity
     */
    protected function saveEntity(Entity $entity)
    {
        $values = $this->entityToValues($entity);

        $sql = 'INSERT INTO ' . $this->tableName . ' VALUES (?, ?, ?)';

        $this->connection->executeQuery($sql, $values);
    }

    /**
     * @param Entity $entity
     * @return array
     */
    protected function entityToValues(Entity $entity)
    {
        return [
            $entity->getUuid()->toNative(),
            $entity->getRelationType()->toNative(),
            $entity->getRelationId()
        ];
    }

    /**
     * @return Entity
     */
    protected function getLastEntity()
    {
        $sql = 'SELECT * FROM ' . $this->tableName;

        $statement = $this->connection->executeQuery($sql);
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $rows ? $this->rowToEntity($rows[count($rows) - 1]) : null;
    }

    /**
     * @param array $row
     * @return Entity
     */
    protected function rowToEntity(array $row)
    {
        return new Entity(
            new UUID($row[SchemaConfigurator::UUID_COLUMN]),
            RelationType::fromNative($row[SchemaConfigurator::RELATION_TYPE_COLUMN]),
            new StringLiteral($row[SchemaConfigurator::RELATION_ID_COLUMN])
        );
    }
}