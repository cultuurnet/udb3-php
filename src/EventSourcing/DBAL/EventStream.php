<?php

namespace CultuurNet\UDB3\EventSourcing\DBAL;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\EventSourcing\EventStreamDecoratorInterface;
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
     * @var int
     */
    protected $previousId;

    /**
     * @var string
     */
    protected $cdbid;

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
    }

    /**
     * @param int $startId
     * @return EventStream
     */
    public function withStartId($startId)
    {
        if (!is_int($startId)) {
            throw new \InvalidArgumentException('StartId should have type int.');
        }

        if (empty($startId)) {
            throw new \InvalidArgumentException('StartId can\'t be empty.');
        }

        $c = clone $this;
        $c->previousId = $startId > 0 ? $startId - 1 : 0;
        return $c;
    }

    /**
     * @param string $cdbid
     * @return EventStream
     */
    public function withCdbid($cdbid)
    {
        if (!is_string($cdbid)) {
            throw new \InvalidArgumentException('Cdbid should have type string.');
        }

        if (empty($cdbid)) {
            throw new \InvalidArgumentException('Cdbid can\'t be empty.');
        }

        $c = clone $this;
        $c->cdbid = $cdbid;
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
        $statement = $this->prepareLoadStatement();

        do {
            $statement->bindValue('previousid', $this->previousId, \PDO::PARAM_INT);
            if ($this->cdbid) {
                $statement->bindValue('uuid', $this->cdbid, \PDO::PARAM_STR);
            }

            $statement->execute();

            $events = [];
            while ($row = $statement->fetch()) {
                $events[] = $this->deserializeEvent($row);
                $this->previousId = $row['id'];
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
            $queryBuilder = $this->connection->createQueryBuilder();

            $queryBuilder->select('id', 'uuid', 'playhead', 'metadata', 'payload', 'recorded_on')
                ->from($this->tableName)
                ->where('id > :previousid')
                ->orderBy('id', 'ASC')
                ->setMaxResults(1);

            if ($this->cdbid) {
                $queryBuilder->andWhere('uuid = :uuid');
            }

            $this->loadStatement = $queryBuilder->execute();
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
