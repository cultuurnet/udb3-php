<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\ReadModel\Index\Doctrine;

use CultuurNet\UDB3\Place\ReadModel\Lookup\PlaceLookupServiceInterface;
use CultuurNet\UDB3\ReadModel\Index\EntityType;
use CultuurNet\UDB3\ReadModel\Index\RepositoryInterface;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use ValueObjects\String\String as StringLiteral;

class DBALRepository implements RepositoryInterface, PlaceLookupServiceInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var StringLiteral
     */
    protected $tableName;

    /**
     * @param Connection $connection
     * @param StringLiteral $tableName
     */
    public function __construct(Connection $connection, StringLiteral $tableName)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    /**
     * @inheritdoc
     */
    public function updateIndex($id, EntityType $entityType, $userId, $name, $postalCode, DateTimeInterface $created = null)
    {
        $this->connection->beginTransaction();

        try {
            if ($this->itemExists($id, $entityType)) {
                $q = $this->connection->createQueryBuilder();
                $q->update($this->tableName->toNative())
                    ->where($this->matchesIdAndEntityType())
                    ->set('uid', ':uid')
                    ->set('title', ':title')
                    ->set('zip', ':zip');

                if ($created instanceof DateTimeInterface) {
                    $q->set('created', ':created');
                }

                $this->setIdAndEntityType($q, $id, $entityType);
                $this->setValues($q, $userId, $name, $postalCode, $created);

                $q->execute();
            } else {
                if (!$created instanceof DateTimeInterface) {
                    $created = new \DateTimeImmutable('now');
                }

                $q = $this->connection->createQueryBuilder();
                $q->insert($this->tableName->toNative())
                    ->values(
                        [
                            'entity_id' => ':entity_id',
                            'entity_type' => ':entity_type',
                            'uid' => ':uid',
                            'title' => ':title',
                            'zip' => ':zip',
                            'created' => ':created',
                        ]
                    );

                $this->setIdAndEntityType($q, $id, $entityType);
                $this->setValues($q, $userId, $name, $postalCode, $created);

                $q->execute();
            }
        } catch (\Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }

        $this->connection->commit();
    }

    /**
     * @param QueryBuilder $q
     * @param string $userId
     * @param string $name
     * @param string $postalCode
     * @param DateTimeInterface $created
     */
    private function setValues(QueryBuilder $q, $userId, $name, $postalCode, DateTimeInterface $created = null)
    {
        $q->setParameter('uid', $userId);
        $q->setParameter('title', $name);
        $q->setParameter('zip', $postalCode);
        if ($created instanceof DateTimeInterface) {
            $q->setParameter('created', $created->getTimestamp());
        }
    }

    /**
     * Returns the WHERE predicates for matching the id and entity_type columns.
     *
     * @return \Doctrine\DBAL\Query\Expression\CompositeExpression
     */
    private function matchesIdAndEntityType()
    {
        $expr = $this->connection->getExpressionBuilder();

        return $expr->andX(
            $expr->eq('entity_id', ':entity_id'),
            $expr->eq('entity_type', ':entity_type')
        );
    }

    /**
     * @param QueryBuilder $q
     * @param string $id
     * @param EntityType $entityType
     */
    private function setIdAndEntityType(
        QueryBuilder $q,
        $id,
        EntityType $entityType
    ) {
        $q->setParameter('entity_id', $id);
        $q->setParameter('entity_type', $entityType->toNative());
    }

    /**
     * @param $id
     * @param EntityType $entityType
     * @return bool
     */
    private function itemExists($id, EntityType $entityType)
    {
        $q = $this->connection->createQueryBuilder();

        $q->select('1')->from($this->tableName->toNative())->where(
            $this->matchesIdAndEntityType()
        );

        $this->setIdAndEntityType($q, $id, $entityType);

        $result = $q->execute();
        $items = $result->fetchAll();

        return count($items) > 0;
    }

    /**
     * @inheritdoc
     */
    public function deleteIndex($id, EntityType $entityType)
    {
        $q = $this->connection->createQueryBuilder();

        $q->delete($this->tableName->toNative())
            ->where($this->matchesIdAndEntityType());

        $this->setIdAndEntityType($q, $id, $entityType);

        $q->execute();
    }

    /**
     * @inheritdoc
     */
    public function findPlacesByPostalCode($postalCode)
    {
        $q = $this->connection->createQueryBuilder();
        $expr = $q->expr();

        $q->select('entity_id')
            ->from($this->tableName->toNative())
            ->where(
                $expr->andX(
                    $expr->eq('entity_type', ':entity_type'),
                    $expr->eq('zip', ':zip')
                )
            );

        $q->setParameter('entity_type', EntityType::PLACE()->toNative());
        $q->setParameter('zip', $postalCode);

        $results = $q->execute();

        return $results->fetchAll(\PDO::FETCH_COLUMN);
    }
}
