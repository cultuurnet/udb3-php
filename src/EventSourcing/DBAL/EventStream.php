<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventSourcing\DBAL;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Serializer\SerializerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\DBALException;

class EventStream
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var SerializerInterface
     */
    protected $payloadSerializer;

    /**
     * @var SerializerInterface
     */
    protected $metadataSerializer;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var Statement
     */
    protected $loadStatement;

    /**
     * @param Connection $connection
     * @param SerializerInterface $payloadSerializer
     * @param SerializerInterface $metadataSerializer
     * @param string $tableName
     */
    public function __construct(
        Connection $connection,
        SerializerInterface $payloadSerializer,
        SerializerInterface $metadataSerializer,
        $tableName
    ) {
        $this->connection = $connection;
        $this->payloadSerializer = $payloadSerializer;
        $this->metadataSerializer = $metadataSerializer;
        $this->tableName = $tableName;
    }

    public function __invoke()
    {
        $statement = $this->prepareLoadStatement();

        $previousId = 0;

        do {
            $statement->bindValue('previousid', $previousId, 'integer');
            $statement->execute();

            $events = [];
            while ($row = $statement->fetch()) {
                $events[] = $this->deserializeEvent($row);
                $previousId = $row['id'];
            }

            if (!empty($events)) {
                yield new DomainEventStream($events);
            }
        } while (!empty($events));
    }

    /**
     * @return Statement
     * @throws DBALException
     */
    protected function prepareLoadStatement()
    {
        if (null === $this->loadStatement) {
            $query = 'SELECT id, uuid, playhead, metadata, payload, recorded_on
                FROM ' . $this->tableName . '
                WHERE id > :previousid
                ORDER BY id ASC
                LIMIT 1';
            $this->loadStatement = $this->connection->prepare($query);
        }

        return $this->loadStatement;
    }

    private function deserializeEvent($row)
    {
        return new DomainMessage(
            $row['uuid'],
            $row['playhead'],
            $this->metadataSerializer->deserialize(json_decode($row['metadata'], true)),
            $this->payloadSerializer->deserialize(json_decode($row['payload'], true)),
            DateTime::fromString($row['recorded_on'])
        );
    }
}
