<?php

namespace CultuurNet\UDB3\Role\ReadModel\Search\Doctrine;

use CultuurNet\UDB3\Role\ReadModel\Search\RepositoryInterface;
use CultuurNet\UDB3\Role\ReadModel\Search\Results;
use Doctrine\DBAL\Connection;
use ValueObjects\String\String as StringLiteral;

class DBALRepository implements RepositoryInterface
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
     * {@inheritdoc}
     */
    public function remove($uuid)
    {
        $q = $this->connection->createQueryBuilder();
        $expr = $this->connection->getExpressionBuilder();

        $q
            ->delete($this->tableName->toNative())
            ->where($expr->eq('uuid', ':role_id'))
            ->setParameter('role_id', $uuid);
        $q->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function save($uuid, $name)
    {
        $q = $this->connection->createQueryBuilder();
        $q
            ->insert($this->tableName->toNative())
            ->values(
                [
                    'uuid' => ':role_id',
                    'name' => ':role_name',
                ]
            )
            ->setParameter('role_id', $uuid)
            ->setParameter('role_name', $name);
        $q->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function search($query = '', $limit = 10, $start = 0)
    {
        $q = $this->connection->createQueryBuilder();
        $expr = $this->connection->getExpressionBuilder();

        // Results.
        $q
            ->select('uuid', 'name')
            ->from($this->tableName->toNative())
            ->orderBy('name', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($start);

        if (!empty($query)) {
            $q->where($expr->like('name', ':role_name'));
            $q->setParameter('role_name', '%' . $query . '%');
        }

        $results = $q->execute()->fetchAll(\PDO::FETCH_ASSOC);

        //Total.
        $q = $this->connection->createQueryBuilder();

        $q
            ->resetQueryParts()
            ->select('COUNT(*) AS total')
            ->from($this->tableName->toNative());

        if (!empty($query)) {
            $q->where($expr->like('name', ':role_name'));
            $q->setParameter('role_name', '%' . $query . '%');
        }

        $total = $q->execute()->fetchColumn();

        return new Results($limit, $results, $total);
    }

    /**
     * {@inheritdoc}
     */
    public function update($uuid, $name)
    {
        $q = $this->connection->createQueryBuilder();
        $expr = $this->connection->getExpressionBuilder();

        $q
            ->update($this->tableName->toNative())
            ->where($expr->eq('uuid', ':role_id'))
            ->set('uuid', ':role_id')
            ->set('name', ':role_name')
            ->setParameter('role_id', $uuid)
            ->setParameter('role_name', $name);
        $q->execute();
    }
}
