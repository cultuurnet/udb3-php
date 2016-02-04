<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 27.10.15
 * Time: 14:04
 */

namespace CultuurNet\UDB3\Event\ReadModel\Permission\Doctrine;

use CultuurNet\UDB3\Offer\ReadModel\Permission\PermissionQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Schema\Schema;
use CultuurNet\UDB3\Offer\ReadModel\Permission\PermissionRepositoryInterface;
use ValueObjects\String\String;

class DBALRepository implements PermissionRepositoryInterface, PermissionQueryInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param String $tableName
     * @param Connection $connection
     */
    public function __construct(String $tableName, Connection $connection)
    {
        $this->tableName = $tableName;
        $this->connection = $connection;
    }

    /**
     * @inheritdoc
     */
    public function getEditableOffers(String $uitId)
    {
        $q = $this->connection->createQueryBuilder();
        $q->select('event_id')
            ->from($this->tableName->toNative())
            ->where('user_id = :userId')
            ->setParameter(':userId', $uitId->toNative());

        $results = $q->execute();

        $events = array();
        while ($id = $results->fetchColumn(0)) {
            $events[] = new String($id);
        }

        return $events;
    }

    /**
     * @inheritdoc
     */
    public function markOfferEditableByUser(String $eventId, String $uitId)
    {
        try {
            $this->connection->insert(
                $this->tableName->toNative(),
                [
                    'event_id' => $eventId->toNative(),
                    'user_id' => $uitId->toNative()
                ]
            );
        } catch (UniqueConstraintViolationException $e) {
            // Intentionally catching database exception occurring when the
            // permission record is already in place.
        }
    }
}
