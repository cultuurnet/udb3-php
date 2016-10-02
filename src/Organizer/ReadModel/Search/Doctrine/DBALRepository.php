<?php

namespace CultuurNet\UDB3\Organizer\ReadModel\Search\Doctrine;

use CultuurNet\UDB3\Organizer\ReadModel\Search\RepositoryInterface;
use CultuurNet\UDB3\Organizer\ReadModel\Search\Results;
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
            ->where($expr->eq(SchemaConfigurator::UUID_COLUMN, ':organizer_id'))
            ->setParameter('organizer_id', $uuid);
        $q->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function save($uuid, $name, $constraint = null)
    {
        $q = $this->connection->createQueryBuilder();
        $q
            ->insert($this->tableName->toNative())
            ->values(
                [
                    SchemaConfigurator::UUID_COLUMN => ':organizer_id',
                    SchemaConfigurator::TITLE_COLUMN => ':organizer_name',
                    SchemaConfigurator::WEBSITE_COLUMN => ':website'
                ]
            )
            ->setParameter('organizer_id', $uuid)
            ->setParameter('organizer_name', $name)
            ->setParameter('website', $constraint);
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
            ->select('uuid', 'title', 'website')
            ->from($this->tableName->toNative())
            ->orderBy('title', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($start);

        if (!empty($query)) {
            $q->where($expr->eq('website', ':website'));
            $q->setParameter('website', '%' . $query . '%');
        }

        $results = $q->execute()->fetchAll(\PDO::FETCH_ASSOC);

        //Total.
        $q = $this->connection->createQueryBuilder();

        $q
            ->resetQueryParts()
            ->select('COUNT(*) AS total')
            ->from($this->tableName->toNative());

        if (!empty($query)) {
            $q->where($expr->eq('website', ':website'));
            $q->setParameter('website', '%' . $query . '%');
        }

        $total = $q->execute()->fetchColumn();

        return new Results($limit, $results, $total);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTitle($uuid, $title)
    {
        $q = $this->connection->createQueryBuilder();
        $expr = $this->connection->getExpressionBuilder();

        $q
            ->update($this->tableName->toNative())
            ->where($expr->eq(SchemaConfigurator::UUID_COLUMN, ':organizer_id'))
            ->set(SchemaConfigurator::UUID_COLUMN, ':organizer_id')
            ->set(SchemaConfigurator::TITLE_COLUMN, ':organizer_name')
            ->setParameter('organizer_id', $uuid)
            ->setParameter('organizer_name', $title);
        $q->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function updateWebsite($uuid, $website)
    {
        $q = $this->connection->createQueryBuilder();
        $expr = $this->connection->getExpressionBuilder();

        $q
            ->update($this->tableName->toNative())
            ->where($expr->eq(SchemaConfigurator::UUID_COLUMN, ':organizer_id'))
            ->set(SchemaConfigurator::UUID_COLUMN, ':organizer_id')
            ->set(SchemaConfigurator::WEBSITE_COLUMN, ':website')
            ->setParameter('organizer_id', $uuid)
            ->setParameter('website', $website);
        $q->execute();
    }
}
