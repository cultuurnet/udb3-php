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
        $sql = $this->connection->getDatabasePlatform()->getTruncateTableSQL($this->tableName);
        $this->connection->exec($sql);
    }
}
