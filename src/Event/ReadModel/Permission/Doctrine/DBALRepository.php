<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 27.10.15
 * Time: 14:04
 */

namespace CultuurNet\UDB3\Event\ReadModel\Permission\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use CultuurNet\UDB3\Event\ReadModel\Permission;

class DBALRepository implements Permission\PermissionRepositoryInterface
{
    protected $tableName = 'event';

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getEditableEvents($uitid, $email)
    {
        $q = $this->connection->createQueryBuilder();
        $q->select('event')
            ->from($this->tableName)
            ->where('createdBy = ?')
            ->setParameter(0, $email);

        $results = $q->execute();

        $events = array();
        while ($id = $results->fetchColumn(0)) {
            $events[] = $id;
        }

        return $events;
    }
}
