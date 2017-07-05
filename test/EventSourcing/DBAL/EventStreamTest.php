<?php

namespace CultuurNet\UDB3\EventSourcing\DBAL;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainEventStreamInterface;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventSourcing\EventStreamDecoratorInterface;
use Broadway\EventStore\DBALEventStore;
use Broadway\Serializer\SerializableInterface;
use Broadway\Serializer\SimpleInterfaceSerializer;
use CultuurNet\UDB3\DBALTestConnectionTrait;

use PHPUnit_Framework_TestCase;

class EventStreamTest extends PHPUnit_Framework_TestCase
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
     */
    public function it_requires_int_type_for_optional_start_id()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('StartId should have type int.');

        $this->eventStream->withStartId('100');
    }

    /**
     * @test
     * @dataProvider invalidStartIdDataProvider
     *
     * @param int $invalidStartId
     */
    public function it_requires_a_value_higher_than_zero_for_optional_start_id($invalidStartId)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('StartId should be higher than 0.');

        $this->eventStream->withStartId($invalidStartId);
    }

    /**
     * @return array
     */
    public function invalidStartIdDataProvider()
    {
        return [
            [0],
            [-1],
            [-0],
        ];
    }

    /**
     * @test
     */
    public function it_requires_string_type_for_optional_cdbid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cdbid should have type string.');

        $this->eventStream->withCdbid(1021);
    }

    /**
     * @test
     */
    public function it_requires_non_empty_value_for_optional_cdbid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cdbid can\'t be empty.');

        $this->eventStream->withCdbid('');
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
        $history = $this->fillHistory();

        if (!is_null($eventStreamDecorator)) {
            $eventStream = $this->eventStream
                ->withDomainEventStreamDecorator($eventStreamDecorator);
        } else {
            $eventStream = $this->eventStream;
        }

        $domainEventStreams = $eventStream();

        $domainEventStreams = iterator_to_array($domainEventStreams);

        $expectedDomainEventStreams = [];
        foreach ($history as $key => $domainMessage) {
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
    public function it_handles_a_specific_cdbid()
    {
        $cdbid = '9B994B6A-FE49-42B0-B67D-F681BE533A7A';
        $history = $this->fillHistory();

        $eventStream = $this->eventStream->withCdbid($cdbid);

        $domainEventStreams = $eventStream();
        $domainEventStreams = iterator_to_array($domainEventStreams);
        $expectedDomainEventStreams = [];
        foreach ($history as $key => $domainMessage) {
            if ($domainMessage->getId() == $cdbid) {
                $expectedDomainEventStreams[] = new DomainEventStream(
                    [
                        $domainMessage,
                    ]
                );
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
    public function it_handles_a_start_id()
    {
        $history = $this->fillHistory();
        $eventStream = $this->eventStream->withStartId(4);

        /** @var EventStream|\Generator $domainEventStreams */
        $domainEventStreams = $eventStream();

        $domainEventStreams = iterator_to_array($domainEventStreams);
        $expectedDomainEventStreams = [];
        foreach ($history as $key => $domainMessage) {
            // The history array is zero-based but sqlite index is one-based.
            // So to start from the 4th element the index needs to be 3.
            if ($key >= 3) {
                $expectedDomainEventStreams[] = new DomainEventStream(
                    [
                        $domainMessage,
                    ]
                );
            }
        }

        $this->assertEquals($expectedDomainEventStreams, $domainEventStreams);
    }

    /**
     * @test
     */
    public function it_returns_the_last_processed_id()
    {
        $this->fillHistory();

        $eventStream = $this->eventStream;
        $domainEventStreams = $eventStream();

        $expectedLastProcessedId = 1;
        while ($domainEventStreams->current()) {
            $this->assertEquals(
                $expectedLastProcessedId++,
                $eventStream->getLastProcessedId()
            );
            $domainEventStreams->next();
        }
    }

    /**
     * @return DomainMessage[]
     */
    private function fillHistory()
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
            ),
        ];

        foreach ($history as $domainMessage) {
            $this->eventStore->append(
                $domainMessage->getId(),
                new DomainEventStream([$domainMessage])
            );
        }

        return $history;
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

class DummyEventStreamDecorator implements EventStreamDecoratorInterface
{
    public function decorateForWrite($aggregateType, $aggregateIdentifier, DomainEventStreamInterface $eventStream)
    {
        $messages = [];

        /** @var DomainMessage $message */
        foreach ($eventStream as $message) {
            $metadata = new Metadata(
                [
                    'mock' => $aggregateType . '::' . $aggregateIdentifier,
                ]
            );

            $messages[] = $message->andMetadata($metadata);
        }

        return new DomainEventStream($messages);
    }
}
