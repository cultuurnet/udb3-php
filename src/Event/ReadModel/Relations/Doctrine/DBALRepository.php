<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\Relations\Doctrine;

use CultuurNet\UDB3\Event\ReadModel\Relations\RepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

class DBALRepository implements RepositoryInterface
{
    protected $tableName = 'event_relations';

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

    public function storeRelations($eventId, $placeId, $organizerId)
    {
        $this->connection->beginTransaction();

        $insert = $this->prepareInsertStatement();
        $insert->bindValue('event', $eventId);
        $insert->bindValue('place', $placeId);
        $insert->bindValue('organizer', $organizerId);
        $insert->execute();

        $this->connection->commit();
    }

    public function removeOrganizer($eventId)
    {
        $transaction = function ($connection) use ($eventId) {
            if ($this->eventHasRelations($connection, $eventId)) {
                $this->updateEventOrganizerRelation($connection, $eventId, null);
            }
        };

        $this->connection->transactional($transaction);
    }

    public function storeOrganizer($eventId, $organizerId)
    {
        $transaction = function ($connection) use ($eventId, $organizerId) {
            if ($this->eventHasRelations($connection, $eventId)) {
                $this->updateEventOrganizerRelation($connection, $eventId, $organizerId);
            } else {
                $this->createEventOrganizerRelation($connection, $eventId, $organizerId);
            }
        };

        $this->connection->transactional($transaction);
    }

    /**
     * @param Connection $connection
     * @param string $eventId
     * @param string $organizerId
     */
    private function createEventOrganizerRelation(
        Connection $connection,
        $eventId,
        $organizerId
    ) {
        $q = $connection
            ->createQueryBuilder()
            ->insert($this->tableName)
            ->values([
                'event' => ':event_id',
                'organizer' => ':organizer_id'
            ])
            ->setParameter('event_id', $eventId)
            ->setParameter('organizer_id', $organizerId);

        $q->execute();
    }

    /**
     * @param Connection $connection
     * @param string $eventId
     * @param string $organizerId
     */
    private function updateEventOrganizerRelation(
        Connection $connection,
        $eventId,
        $organizerId
    ) {
        $q = $connection
            ->createQueryBuilder()
            ->update($this->tableName)
            ->where('event = :event_id')
            ->set('organizer', ':organizer_id')
            ->setParameter('event_id', $eventId)
            ->setParameter('organizer_id', $organizerId);

        $q->execute();
    }

    /**
     * @param Connection $connection
     * @param string $id
     * @return bool
     */
    private function eventHasRelations(
        Connection $connection,
        $id
    ) {
        $q = $connection->createQueryBuilder();

        $q->select('1')
            ->from($this->tableName, 'relation')
            ->where('relation.event = :event_id')
            ->setParameter('event_id', $id);

        $result = $q->execute();
        $relations = $result->fetchAll();

        return count($relations) > 0;
    }

    private function prepareInsertStatement()
    {
        $table = $this->connection->quoteIdentifier($this->tableName);
        return $this->connection->prepare(
            "INSERT INTO {$table} SET
              event = :event,
              place = :place,
              organizer = :organizer
            ON DUPLICATE KEY UPDATE
              place = :place,
              organizer=:organizer"
        );
    }

    public function getEventsLocatedAtPlace($placeId)
    {
        $q = $this->connection->createQueryBuilder();
        $q->select('event')
          ->from($this->tableName)
          ->where('place = ?')
          ->setParameter(0, $placeId);

        $results = $q->execute();

        $events = array();
        while ($id = $results->fetchColumn(0)) {
            $events[] = $id;
        }

        return $events;
    }

    public function getEventsOrganizedByOrganizer($organizerId)
    {
        $q = $this->connection->createQueryBuilder();
        $q
            ->select('event')
            ->from($this->tableName)
            ->where('organizer = ?')
            ->setParameter(0, $organizerId);

        $results = $q->execute();

        $events = array();
        while ($id = $results->fetchColumn(0)) {
            $events[] = $id;
        }

        return $events;
    }

    public function removeRelations($eventId)
    {
      // @todo implement this for non-drupal.
    }

    /**
     * @return \Doctrine\DBAL\Schema\Table|null
     */
    public function configureSchema(Schema $schema)
    {
        if ($schema->hasTable($this->tableName)) {
            return null;
        }

        return $this->configureTable();
    }

    public function configureTable()
    {
        $schema = new Schema();

        $table = $schema->createTable($this->tableName);

        $table->addColumn(
            'event',
            'string',
            array('length' => 36, 'notnull' => false)
        );
        $table->addColumn(
            'organizer',
            'string',
            array('length' => 36, 'notnull' => false)
        );
        $table->addColumn(
            'place',
            'string',
            array('length' => 36, 'notnull' => false)
        );

        $table->setPrimaryKey(array('event'));

        return $table;
    }
}
