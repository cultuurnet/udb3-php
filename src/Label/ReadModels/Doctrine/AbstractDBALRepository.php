<?php

namespace CultuurNet\UDB3\Label\ReadModels\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use ValueObjects\String\String as StringLiteral;

abstract class AbstractDBALRepository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var StringLiteral
     */
    private $tableName;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    public function __construct(
        Connection $connection,
        StringLiteral $tableName
    ) {
        $this->connection = $connection;
        $this->tableName = $tableName;
        $this->queryBuilder = $this->connection->createQueryBuilder();
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return StringLiteral
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }
}
