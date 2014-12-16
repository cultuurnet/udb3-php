<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Doctrine\Event\ReadModel\Relations;


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

    private function prepareInsertStatement() {
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
        // TODO: Implement getEventsLocatedAtPlace() method.
    }

    public function getEventsOrganizedByOrganizer($organizerId)
    {
        // TODO: Implement getEventsOrganizedByOrganizer() method.
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

        $table->addColumn('event', 'string', array('length' => 32));
        $table->addColumn('organizer', 'string', array('length' => 32));
        $table->addColumn('place', 'string', array('length' => 32));

        $table->setPrimaryKey(array('event_id'));

        return $table;
    }

}
