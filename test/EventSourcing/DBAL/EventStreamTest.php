<?php

namespace CultuurNet\UDB3\EventSourcing\DBAL;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventSourcing\EventStreamDecoratorInterface;
use Broadway\EventStore\DBALEventStore;
use Broadway\EventStore\EventStoreInterface;
use Broadway\Serializer\SimpleInterfaceSerializer;
use CultuurNet\UDB3\DBALTestConnectionTrait;

class EventStreamTest extends \PHPUnit_Framework_TestCase
{
    use DBALTestConnectionTrait;

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
        $table = 'events';
        $payloadSerializer = new SimpleInterfaceSerializer();
        $metadataSerializer = new SimpleInterfaceSerializer();

        $this->eventStore = new DBALEventStore(
            $this->getConnection(),
            $payloadSerializer,
            $metadataSerializer,
            $table
        );

        $schemaManager = $this->getConnection()->getSchemaManager();
        $schema = $schemaManager->createSchema();

        $schemaManager->createTable(
            $this->eventStore->configureSchema($schema)
        );

        $this->eventStream = new EventStream(
            $this->getConnection(),
            $payloadSerializer,
            $metadataSerializer,
            $table
        );
    }

    /**
     * @test
     * @dataProvider eventStreamDecoratorDataProvider
     * @param EventStreamDecoratorInterface|null $eventStreamDecorator
     * @param array $expectedDecoratedMetadata
     */
    public function it_retrieves_all_events_from_the_event_store(
        EventStreamDecoratorInterface $eventStreamDecorator = null,
        array $expectedDecoratedMetadata = []
    ) {
        $domainMessages = $this->createDomainMessages();
        $this->appendDomainMessages($this->eventStore, $domainMessages);

        if (!is_null($eventStreamDecorator)) {
            $eventStream = $this->eventStream
                ->withDomainEventStreamDecorator($eventStreamDecorator);
        } else {
            $eventStream = $this->eventStream;
        }

        $domainEventStreams = $eventStream();

        $domainEventStreams = iterator_to_array($domainEventStreams);

        $expectedDomainEventStreams = [];
        foreach ($domainMessages as $key => $domainMessage) {
            $expectedDomainMessage = $domainMessage->andMetadata($expectedDecoratedMetadata[$key]);
            $expectedDomainEventStreams[] = new DomainEventStream([$expectedDomainMessage]);
        }

        $this->assertEquals(
            $expectedDomainEventStreams,
            $domainEventStreams
        );
    }

    /**
     * @return array
     */
    public function eventStreamDecoratorDataProvider()
    {
        return [
            // No event stream decorator should result in no extra metadata.
            [
                null,
                [
                    0 => new Metadata(),
                    1 => new Metadata(),
                    2 => new Metadata(),
                    3 => new Metadata(),
                    4 => new Metadata(),
                    5 => new Metadata(),
                    6 => new Metadata(),
                    7 => new Metadata(),
                    8 => new Metadata(),
                ],
            ],
            // The dummy event stream decorator should add some extra mock
            // metadata.
            [
                new DummyEventStreamDecorator(),
                [
                    0 => new Metadata(
                        ['mock' => 'CultuurNet\UDB3\EventSourcing\DBAL\DummyEvent::F68E71A1-DBB0-4542-AEE5-BD937E095F74']
                    ),
                    1 => new Metadata(
                        ['mock' => 'CultuurNet\UDB3\EventSourcing\DBAL\DummyEvent::F68E71A1-DBB0-4542-AEE5-BD937E095F74']
                    ),
                    2 => new Metadata(
                        ['mock' => 'CultuurNet\UDB3\EventSourcing\DBAL\DummyEvent::011A02C5-D395-47C1-BEBE-184840A2C961']
                    ),
                    3 => new Metadata(
                        ['mock' => 'CultuurNet\UDB3\EventSourcing\DBAL\DummyEvent::9B994B6A-FE49-42B0-B67D-F681BE533A7A']
                    ),
                    4 => new Metadata(
                        ['mock' => 'CultuurNet\UDB3\EventSourcing\DBAL\DummyEvent::F68E71A1-DBB0-4542-AEE5-BD937E095F74']
                    ),
                    5 => new Metadata(
                        ['mock' => 'CultuurNet\UDB3\EventSourcing\DBAL\DummyEvent::F68E71A1-DBB0-4542-AEE5-BD937E095F74']
                    ),
                    6 => new Metadata(
                        ['mock' => 'CultuurNet\UDB3\EventSourcing\DBAL\DummyEvent::F68E71A1-DBB0-4542-AEE5-BD937E095F74']
                    ),
                    7 => new Metadata(
                        ['mock' => 'CultuurNet\UDB3\EventSourcing\DBAL\DummyEvent::F68E71A1-DBB0-4542-AEE5-BD937E095F74']
                    ),
                    8 => new Metadata(
                        ['mock' => 'CultuurNet\UDB3\EventSourcing\DBAL\DummyEvent::F68E71A1-DBB0-4542-AEE5-BD937E095F74']
                    ),
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function it_can_return_the_start_id()
    {
        $table = 'events';
        $payloadSerializer = new SimpleInterfaceSerializer();
        $metadataSerializer = new SimpleInterfaceSerializer();
        $startId = 101;

        $eventStream = new EventStream(
            $this->getConnection(),
            $payloadSerializer,
            $metadataSerializer,
            $table
        );

        $eventStream = $eventStream->withStartId($startId);

        $expectedPreviousId = 100;

        $this->assertEquals(
            $expectedPreviousId,
            $eventStream->getPreviousId()
        );
    }

    /**
     * @test
     */
    public function it_can_handle_a_start_id()
    {
        $domainMessages = $this->createDomainMessages();
        $this->appendDomainMessages($this->eventStore, $domainMessages);

        $startId = 3;
        $eventStream = $this->eventStream->withStartId($startId);

        $domainEventStreams = $eventStream();

        $domainEventStreams = iterator_to_array($domainEventStreams);

        $expectedDomainEventStreams = [];
        foreach ($domainMessages as $key => $domainMessage) {
            if ($key >= $startId - 1) {
                $expectedDomainEventStreams[] = new DomainEventStream([$domainMessage]);
            }
        }

        $this->assertEquals(
            $expectedDomainEventStreams,
            $domainEventStreams
        );
    }

    /**
     * @test
     */
    public function it_can_handle_an_aggregate_type()
    {
        $aggregateTypes = ['event', 'place', 'organizer'];

        $stores = [];
        foreach ($aggregateTypes as $aggregateType) {
            $stores[$aggregateType] = $this->createAggregateAwareDBALEventStore($aggregateType);
        }

        $schemaManager = $this->getConnection()->getSchemaManager();
        $schema = $schemaManager->createSchema();

        $table = $stores['event']->configureSchema($schema);
        if ($table) {
            $schemaManager->createTable($table);
        }

        $domainMessages = $this->createDomainMessages();
        foreach ($domainMessages as $domainMessage) {
            $metadataAsArray = $domainMessage->getMetadata()->serialize();
            $eventStore = $stores[$metadataAsArray['aggregate_type']];
            $eventStore->append(
                $domainMessage->getId(),
                new DomainEventStream([$domainMessage])
            );
        }

        $eventStream = new EventStream(
            $this->getConnection(),
            new SimpleInterfaceSerializer(),
            new SimpleInterfaceSerializer(),
            'event_store'
        );

        foreach ($aggregateTypes as $aggregateType) {
            $this->checkEventStream($eventStream, $domainMessages, $aggregateType);
        }
    }

    /**
     * @return DomainMessage[]
     */
    private function createDomainMessages()
    {
        $idOfEntityA = 'F68E71A1-DBB0-4542-AEE5-BD937E095F74';
        $idOfEntityB = '011A02C5-D395-47C1-BEBE-184840A2C961';
        $idOfEntityC = '9B994B6A-FE49-42B0-B67D-F681BE533A7A';

        $domainMessages = [
            0 => new DomainMessage(
                $idOfEntityA,
                1,
                new Metadata(['aggregate_type' => 'event']),
                new DummyEvent(
                    $idOfEntityA,
                    'test 123'
                ),
                DateTime::fromString('2015-01-02T08:30:00+0100')
            ),
            1 => new DomainMessage(
                $idOfEntityA,
                2,
                new Metadata(['aggregate_type' => 'event']),
                new DummyEvent(
                    $idOfEntityA,
                    'test 123 456'
                ),
                DateTime::fromString('2015-01-02T08:40:00+0100')
            ),
            2 => new DomainMessage(
                $idOfEntityB,
                1,
                new Metadata(['aggregate_type' => 'place']),
                new DummyEvent(
                    $idOfEntityB,
                    'entity b test content'
                ),
                DateTime::fromString('2015-01-02T08:41:00+0100')
            ),
            3 => new DomainMessage(
                $idOfEntityC,
                1,
                new Metadata(['aggregate_type' => 'organizer']),
                new DummyEvent(
                    $idOfEntityC,
                    'entity c test content'
                ),
                DateTime::fromString('2015-01-02T08:42:30+0100')
            ),
            4 => new DomainMessage(
                $idOfEntityA,
                3,
                new Metadata(['aggregate_type' => 'event']),
                new DummyEvent(
                    $idOfEntityA,
                    'entity a test content'
                ),
                DateTime::fromString('2015-01-03T16:00:01+0100')
            ),
            5 => new DomainMessage(
                $idOfEntityA,
                4,
                new Metadata(['aggregate_type' => 'event']),
                new DummyEvent(
                    $idOfEntityA,
                    'entity a test content playhead 4'
                ),
                DateTime::fromString('2015-01-03T17:00:01+0100')
            ),
            6 => new DomainMessage(
                $idOfEntityA,
                5,
                new Metadata(['aggregate_type' => 'event']),
                new DummyEvent(
                    $idOfEntityA,
                    'entity a test content playhead 5'
                ),
                DateTime::fromString('2015-01-03T18:00:01+0100')
            ),
            7 => new DomainMessage(
                $idOfEntityA,
                6,
                new Metadata(['aggregate_type' => 'event']),
                new DummyEvent(
                    $idOfEntityA,
                    'entity a test content playhead 6'
                ),
                DateTime::fromString('2015-01-03T18:30:01+0100')
            ),
            8 => new DomainMessage(
                $idOfEntityA,
                7,
                new Metadata(['aggregate_type' => 'event']),
                new DummyEvent(
                    $idOfEntityA,
                    'entity a test content playhead 7'
                ),
                DateTime::fromString('2015-01-03T19:45:00+0100')
            )
        ];

        return $domainMessages;
    }

    /**
     * @param DomainMessage[] $domainMessages
     * @param EventStoreInterface $eventStore
     */
    private function appendDomainMessages(EventStoreInterface $eventStore, array $domainMessages)
    {
        foreach ($domainMessages as $domainMessage) {
            $eventStore->append(
                $domainMessage->getId(),
                new DomainEventStream([$domainMessage])
            );
        }
    }

    /**
     * @param string $aggregateType
     * @return AggregateAwareDBALEventStore
     */
    private function createAggregateAwareDBALEventStore($aggregateType)
    {
        return new AggregateAwareDBALEventStore(
            $this->getConnection(),
            new SimpleInterfaceSerializer(),
            new SimpleInterfaceSerializer(),
            'event_store',
            $aggregateType
        );
    }

    /**
     * @param EventStream $eventStream
     * @param DomainMessage[] $domainMessages
     * @param string $aggregateType
     */
    private function checkEventStream(
        EventStream $eventStream,
        array $domainMessages,
        $aggregateType
    ) {
        $eventStream = $eventStream->withAggregateType($aggregateType);

        $domainEventStreams = $eventStream();
        $domainEventStreams = iterator_to_array($domainEventStreams);

        $expectedDomainEventStreams = [];
        foreach ($domainMessages as $key => $domainMessage) {
            $metadataAsArray = $domainMessage->getMetadata()->serialize();
            if ($metadataAsArray['aggregate_type'] === $aggregateType) {
                $expectedDomainEventStreams[] = new DomainEventStream([$domainMessage]);
            }
        }

        $this->assertEquals(
            $expectedDomainEventStreams,
            $domainEventStreams
        );
    }
}
