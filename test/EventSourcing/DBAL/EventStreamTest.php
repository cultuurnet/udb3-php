<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventSourcing\DBAL;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\DBALEventStore;
use Broadway\Serializer\SerializableInterface;
use Broadway\Serializer\SimpleInterfaceSerializer;
use Doctrine\DBAL\DriverManager;
use PDO;
use PHPUnit_Framework_TestCase;

class EventStreamTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DBALEventStore
     */
    private $eventStore;

    /**
     * @var EventStream
     */
    private $eventStream;

    public function setUp()
    {
        if (!class_exists('PDO')) {
            $this->markTestSkipped('PDO is required to run this test.');
        }

        $availableDrivers = PDO::getAvailableDrivers();
        if (!in_array('sqlite', $availableDrivers)) {
            $this->markTestSkipped(
                'PDO sqlite driver is required to run this test.'
            );
        }

        $connection = DriverManager::getConnection(
            [
                'url' => 'sqlite:///:memory:',
            ]
        );

        $table = 'events';
        $payloadSerializer = new SimpleInterfaceSerializer();
        $metadataSerializer = new SimpleInterfaceSerializer();

        $this->eventStore = new DBALEventStore(
            $connection,
            $payloadSerializer,
            $metadataSerializer,
            $table
        );

        $schemaManager = $connection->getSchemaManager();
        $schema = $schemaManager->createSchema();

        $schemaManager->createTable(
            $this->eventStore->configureSchema($schema)
        );

        $this->eventStream = new EventStream(
            $connection,
            $payloadSerializer,
            $metadataSerializer,
            $table
        );
    }
    /**
     * @test
     */
    public function it_retrieves_all_events_from_the_event_store()
    {
        $idOfEntityA = 'F68E71A1-DBB0-4542-AEE5-BD937E095F74';
        $idOfEntityB = '011A02C5-D395-47C1-BEBE-184840A2C961';
        $idOfEntityC = '9B994B6A-FE49-42B0-B67D-F681BE533A7A';

        /** @var DomainMessage[] $history */
        $history = [
            0 => new DomainMessage(
                'F68E71A1-DBB0-4542-AEE5-BD937E095F74',
                1,
                new Metadata(),
                new DummyEvent(
                    'F68E71A1-DBB0-4542-AEE5-BD937E095F74',
                    'test 123'
                ),
                DateTime::fromString('2015-01-02T08:30:00+0100')
            ),
            1 => new DomainMessage(
                'F68E71A1-DBB0-4542-AEE5-BD937E095F74',
                2,
                new Metadata(),
                new DummyEvent(
                    'F68E71A1-DBB0-4542-AEE5-BD937E095F74',
                    'test 123 456'
                ),
                DateTime::fromString('2015-01-02T08:40:00+0100')
            ),
            2 => new DomainMessage(
                $idOfEntityB,
                1,
                new Metadata(),
                new DummyEvent(
                    $idOfEntityB,
                    'entity b test content'
                ),
                DateTime::fromString('2015-01-02T08:41:00+0100')
            ),
            3 => new DomainMessage(
                $idOfEntityC,
                1,
                new Metadata(),
                new DummyEvent(
                    $idOfEntityC,
                    'entity c test content'
                ),
                DateTime::fromString('2015-01-02T08:42:30+0100')
            ),
            4 => new DomainMessage(
                $idOfEntityA,
                3,
                new Metadata(),
                new DummyEvent(
                    $idOfEntityA,
                    'entity a test content'
                ),
                DateTime::fromString('2015-01-03T16:00:01+0100')
            ),
            5 => new DomainMessage(
                $idOfEntityA,
                4,
                new Metadata(),
                new DummyEvent(
                    $idOfEntityA,
                    'entity a test content playhead 4'
                ),
                DateTime::fromString('2015-01-03T17:00:01+0100')
            ),
            6 => new DomainMessage(
                $idOfEntityA,
                5,
                new Metadata(),
                new DummyEvent(
                    $idOfEntityA,
                    'entity a test content playhead 5'
                ),
                DateTime::fromString('2015-01-03T18:00:01+0100')
            ),
            7 => new DomainMessage(
                $idOfEntityA,
                6,
                new Metadata(),
                new DummyEvent(
                    $idOfEntityA,
                    'entity a test content playhead 6'
                ),
                DateTime::fromString('2015-01-03T18:30:01+0100')
            ),
            8 => new DomainMessage(
                $idOfEntityA,
                7,
                new Metadata(),
                new DummyEvent(
                    $idOfEntityA,
                    'entity a test content playhead 7'
                ),
                DateTime::fromString('2015-01-03T19:45:00+0100')
            )
        ];

        foreach ($history as $domainMessage) {
            $this->eventStore->append(
                $domainMessage->getId(),
                new DomainEventStream([$domainMessage])
            );
        }

        $eventStream = $this->eventStream;
        $domainEventStreams = $eventStream();

        $domainEventStreams = iterator_to_array($domainEventStreams);

        $expectedDomainEventStreams = [];
        foreach ($history as $key => $domainMessage) {
            $expectedDomainEventStreams[] = new DomainEventStream([$domainMessage]);
        }

        $this->assertEquals(
            $expectedDomainEventStreams,
            $domainEventStreams
        );
    }
}

class DummyEvent implements SerializableInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $content;

    /**
     * @param string $id
     * @param string $content
     */
    public function __construct($id, $content)
    {
        $this->id = $id;
        $this->content = $content;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['id'],
            $data['content']
        );
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
        ];
    }
}
