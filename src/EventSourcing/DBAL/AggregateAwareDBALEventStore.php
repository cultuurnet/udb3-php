<?php

namespace CultuurNet\UDB3\EventSourcing\DBAL;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainEventStreamInterface;
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\DBALEventStoreException;
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventStore\EventStreamNotFoundException;
use Broadway\EventStore\Exception\InvalidIdentifierException;
use Broadway\Serializer\SerializerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Version;
use Rhumsaa\Uuid\Uuid;

class AggregateAwareDBALEventStore implements EventStoreInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var SerializerInterface
     */
    private $payloadSerializer;

    /**
     * @var SerializerInterface
     */
    private $metadataSerializer;

    /**
     * @var null
     */
    private $loadStatement = null;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $aggregateType;

    /**
     * @var bool
     */
    private $useBinary;

    /**
     * @param Connection $connection
     * @param SerializerInterface $payloadSerializer
     * @param SerializerInterface $metadataSerializer
     * @param string $tableName
     * @param string $aggregateType
     * @param bool $useBinary
     */
    public function __construct(
        Connection $connection,
        SerializerInterface $payloadSerializer,
        SerializerInterface $metadataSerializer,
        $tableName,
        $aggregateType,
        $useBinary = false
    ) {
        $this->connection         = $connection;
        $this->payloadSerializer  = $payloadSerializer;
        $this->metadataSerializer = $metadataSerializer;
        $this->tableName          = $tableName;
        $this->aggregateType      = $aggregateType;
        $this->useBinary          = (bool) $useBinary;

        if ($this->useBinary && Version::compare('2.5.0') >= 0) {
            throw new \InvalidArgumentException(
                'The Binary storage is only available with Doctrine DBAL >= 2.5.0'
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        $statement = $this->prepareLoadStatement();
        $statement->bindValue('uuid', $this->convertIdentifierToStorageValue($id));
        $statement->execute();

        $events = array();
        while ($row = $statement->fetch()) {
            if ($this->useBinary) {
                $row['uuid'] = $this->convertStorageValueToIdentifier($row['uuid']);
            }
            $events[] = $this->deserializeEvent($row);
        }

        if (empty($events)) {
            throw new EventStreamNotFoundException(sprintf('EventStream not found for aggregate with id %s', $id));
        }

        return new DomainEventStream($events);
    }

    /**
     * {@inheritDoc}
     */
    public function append($id, DomainEventStreamInterface $eventStream)
    {
        // noop to ensure that an error will be thrown early if the ID
        // is not something that can be converted to a string. If we
        // let this move on without doing this DBAL will eventually
        // give us a hard time but the true reason for the problem
        // will be obfuscated.
        $id = (string) $id;

        $this->connection->beginTransaction();

        try {
            foreach ($eventStream as $domainMessage) {
                $this->insertMessage($this->connection, $domainMessage);
            }

            $this->connection->commit();
        } catch (DBALException $exception) {
            $this->connection->rollback();

            throw DBALEventStoreException::create($exception);
        }
    }

    /**
     * @param Connection $connection
     * @param DomainMessage $domainMessage
     */
    private function insertMessage(Connection $connection, DomainMessage $domainMessage)
    {
        $data = array(
            'uuid'           => $this->convertIdentifierToStorageValue((string) $domainMessage->getId()),
            'playhead'       => $domainMessage->getPlayhead(),
            'metadata'       => json_encode($this->metadataSerializer->serialize($domainMessage->getMetadata())),
            'payload'        => json_encode($this->payloadSerializer->serialize($domainMessage->getPayload())),
            'recorded_on'    => $domainMessage->getRecordedOn()->toString(),
            'type'           => $domainMessage->getType(),
            'aggregate_type' => $this->aggregateType
        );

        $connection->insert($this->tableName, $data);
    }

    /**
     * @param Schema $schema
     * @return Table|null
     */
    public function configureSchema(Schema $schema)
    {
        if ($schema->hasTable($this->tableName)) {
            return null;
        }

        return $this->configureTable();
    }

    /**
     * @return mixed
     */
    public function configureTable()
    {
        $schema = new Schema();

        $uuidColumnDefinition = array(
            'type'   => 'guid',
            'params' => array(
                'length' => 36,
            ),
        );

        if ($this->useBinary) {
            $uuidColumnDefinition['type']   = 'binary';
            $uuidColumnDefinition['params'] = array(
                'length' => 16,
                'fixed'  => true,
            );
        }

        $table = $schema->createTable($this->tableName);

        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('uuid', $uuidColumnDefinition['type'], $uuidColumnDefinition['params']);
        $table->addColumn('playhead', 'integer', array('unsigned' => true));
        $table->addColumn('payload', 'text');
        $table->addColumn('metadata', 'text');
        $table->addColumn('recorded_on', 'string', array('length' => 32));
        $table->addColumn('type', 'string', array('length' => 128));
        $table->addColumn('aggregate_type', 'string', array('length' => 128));

        $table->setPrimaryKey(array('id'));
        $table->addUniqueIndex(array('uuid', 'playhead'));

        return $table;
    }

    /**
     * @return \Doctrine\DBAL\Driver\Statement|null
     */
    private function prepareLoadStatement()
    {
        if (null === $this->loadStatement) {
            $query = 'SELECT uuid, playhead, metadata, payload, recorded_on
                FROM ' . $this->tableName . '
                WHERE uuid = :uuid
                ORDER BY playhead ASC';
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
            BroadwayDateTime::fromString($row['recorded_on'])
        );
    }

    /**
     * @param $id
     * @return mixed
     */
    private function convertIdentifierToStorageValue($id)
    {
        if ($this->useBinary) {
            try {
                return Uuid::fromString($id)->getBytes();
            } catch (\Exception $e) {
                throw new InvalidIdentifierException(
                    'Only valid UUIDs are allowed to by used with the binary storage mode.'
                );
            }
        }

        return $id;
    }

    /**
     * @param $id
     * @return mixed
     */
    private function convertStorageValueToIdentifier($id)
    {
        if ($this->useBinary) {
            try {
                return Uuid::fromBytes($id)->toString();
            } catch (\Exception $e) {
                throw new InvalidIdentifierException(
                    'Could not convert binary storage value to UUID.'
                );
            }
        }

        return $id;
    }
}
