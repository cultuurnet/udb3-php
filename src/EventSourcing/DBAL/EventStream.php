<?php

namespace CultuurNet\UDB3\EventSourcing\DBAL;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\EventSourcing\EventStreamDecoratorInterface;
use Broadway\Serializer\SerializerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Query\QueryBuilder;

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
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var int
     */
    protected $previousId;

    /**
     * @var string
     */
    protected $aggregateType;

    /**
     * @var EventStreamDecoratorInterface
     */
    private $domainEventStreamDecorator;

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
        $this->previousId = 0;
        $this->primaryKey = 'id';
        $this->aggregateType = '';
        $this->domainEventStreamDecorator = null;
    }

    /**
     * @param int $startId
     * @return EventStream
     */
    public function withStartId($startId)
    {
        $c = clone $this;
        $c->previousId = $startId - 1;
        return $c;
    }

    /**
     * @param string $aggregateType
     * @return EventStream
     */
    public function withAggregateType($aggregateType)
    {
        $c = clone $this;
        $c->aggregateType = $aggregateType;
        return $c;
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
        $queryBuilder = $this->prepareLoadQuery();

        do {
            $queryBuilder->setParameter(':previousId', $this->previousId);
            $statement = $queryBuilder->execute();

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
     * @return QueryBuilder
     * @throws DBALException
     */
    protected function prepareLoadQuery()
    {
        if (null === $this->queryBuilder) {
            $this->queryBuilder = $this->connection->createQueryBuilder();

            $this->queryBuilder->select(
                [
                    $this->primaryKey,
                    'uuid',
                    'playhead',
                    'payload',
                    'metadata',
                    'recorded_on'
                ]
            )
                ->from($this->tableName)
                ->where($this->primaryKey . ' > :previousId')
                ->orderBy($this->primaryKey, 'ASC')
                ->setMaxResults(1);

            if (!empty($this->aggregateType)) {
                $this->queryBuilder->andWhere('aggregate_type = :aggregate_type');
                $this->queryBuilder->setParameter('aggregate_type', $this->aggregateType);
            }
        }

        return $this->queryBuilder;
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
