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

    public function storeOrganizer($eventId, $organizerId)
    {
        $this->connection->beginTransaction();

        // ignore place so it does not get overwritten
        $insert = $this->prepareInsertStatement(true);
        $insert->bindValue('event', $eventId);
        // add an empty place reference incase a new relation has to be created
        $insert->bindValue('place', null);
        $insert->bindValue('organizer', $organizerId);
        $insert->execute();

        $this->connection->commit();
    }

    /**
     * @param boolean|null $ignorePlace
     * @return \Doctrine\DBAL\Driver\Statement
     */
    private function prepareInsertStatement($ignorePlace = null)
    {
        $table = $this->connection->quoteIdentifier($this->tableName);
        $statement =
          "INSERT INTO {$table} SET
              event = :event,
              place = :place,
              organizer = :organizer
            ON DUPLICATE KEY UPDATE
              organizer = :organizer";

        if ($ignorePlace) {
            $statement = $statement . ", place = :place";
        }

        return $this->connection->prepare($statement);
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
