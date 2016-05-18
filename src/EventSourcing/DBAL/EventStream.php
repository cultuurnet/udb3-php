<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventSourcing\DBAL;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\EventSourcing\EventStreamDecoratorInterface;
use Broadway\Serializer\SerializerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;

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
     * @var int
     */
    protected $previousId;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * @var EventStreamDecoratorInterface
     */
    private $domainEventStreamDecorator;

    /**
     * @param Connection $connection
     * @param SerializerInterface $payloadSerializer
     * @param SerializerInterface $metadataSerializer
     * @param string $tableName
     * @param int $startId
     * @param string $primaryKey
     */
    public function __construct(
        Connection $connection,
        SerializerInterface $payloadSerializer,
        SerializerInterface $metadataSerializer,
        $tableName,
        $startId = 0,
        $primaryKey = 'id'
    ) {
        $this->connection = $connection;
        $this->payloadSerializer = $payloadSerializer;
        $this->metadataSerializer = $metadataSerializer;
        $this->tableName = $tableName;
        $this->previousId = $startId > 0 ? $startId - 1 : 0;

        $this->primaryKey = $primaryKey;

        $this->domainEventStreamDecorator = null;
    }

    /**
     * @param EventStreamDecoratorInterface $domainEventStreamDecorator
     * @return EventStream
     */
    public function withDomainEventStreamDecorator(EventStreamDecoratorInterface $domainEventStreamDecorator)
    {
        $c = clone $this;
        $c->domainEventStreamDecorator = $domainEventStreamDecorator;
        return $c;
    }

    public function __invoke()
    {
        $statement = $this->prepareLoadStatement();

        do {
            $statement->bindValue('previousid', $this->previousId, 'integer');
            $statement->execute();

            $events = [];
            while ($row = $statement->fetch()) {
                $events[] = $this->deserializeEvent($row);
                $this->previousId = $row[$this->primaryKey];
            }

            /* @var DomainMessage[] $events */
            if (!empty($events)) {
                $event = $events[0];
                $domainEventStream = new DomainEventStream($events);

                if (!is_null($this->domainEventStreamDecorator)) {
                    // Because the load statement always returns one row at a
                    // time, and we always wrap a single domain message in a
                    // stream as a result, we can simply get the aggregate type
                    // and aggregate id from the first domain message in the
                    // stream.
                    $domainEventStream = $this->domainEventStreamDecorator->decorateForWrite(
                        get_class($event->getPayload()),
                        $event->getId(),
                        $domainEventStream
                    );
                }

                yield $domainEventStream;
            }
        } while (!empty($events));
    }

    /**
     * @return int
     */
    public function getPreviousId()
    {
        return $this->previousId;
    }

    /**
     * @return Statement
     * @throws DBALException
     */
    protected function prepareLoadStatement()
    {
        if (null === $this->loadStatement) {
            $id = $this->primaryKey;
            $query = "SELECT $id, uuid, playhead, metadata, payload, recorded_on
                FROM $this->tableName
                WHERE $id > :previousid
                ORDER BY $id ASC
                LIMIT 1";

            $this->loadStatement = $this->connection->prepare($query);
        }

        return $this->loadStatement;
    }

    /**
     * @param $row
     * @return DomainMessage
     */
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
