<?php

namespace CultuurNet\UDB3\Storage;

use Doctrine\DBAL\Connection;

/**
 * Class DBALPurgeService
 * @package CultuurNet\UDB3\Storage
 */
class DBALPurgeService implements PurgeServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $tableName;

    /**
     * DBALPurgeService constructor.
     * @param Connection $connection
     * @param string $tableName
     */
    public function __construct(
        $connection,
        $tableName
    ) {
        $this->connection = $connection;

        $this->tableName = $tableName;
    }

    public function purgeAll()
    {
        $this->delete();

        $this->resetAutoIncrement();
    }

    private function delete()
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->delete($this->tableName);
        $queryBuilder->execute();
    }

    private function resetAutoIncrement()
    {
        $sql = 'ALTER TABLE ' . $this->tableName . ' auto_increment = 1';
        $this->connection->exec($sql);
    }
}
