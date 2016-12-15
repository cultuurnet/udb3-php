<?php

namespace CultuurNet\UDB3\EventSourcing\DBAL;

use Broadway\Serializer\SerializerInterface;
use CultuurNet\UDB3\DBALTestConnectionTrait;

class AggregateAwareDBALEventStoreTest extends \PHPUnit_Framework_TestCase
{
    use DBALTestConnectionTrait;

    /**
     * @var AggregateAwareDBALEventStore
     */
    private $aggregateAwareDBALEventStore;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payloadSerializer;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataSerializer;

    protected function setUp()
    {
        $this->payloadSerializer = $this->getMock(SerializerInterface::class);

        $this->metadataSerializer = $this->getMock(SerializerInterface::class);

        $this->aggregateAwareDBALEventStore = new AggregateAwareDBALEventStore(
            $this->getConnection(),
            $this->payloadSerializer,
            $this->metadataSerializer,
            'event_store',
            'place'
        );

        $this->createTable();
    }

    public function it_can_load_an_aggregate_of_a_certain_type()
    {
        $this->assertTrue(false);
    }

    public function it_throws_an_exception_when_loading_a_non_existing_aggregate()
    {
        $this->assertTrue(false);
    }

    public function it_throws_an_exception_when_an_id_cannot_be_converted_to_a_string()
    {
        $this->assertTrue(false);
    }

    public function it_can_append_to_an_aggregate_of_a_certain_type()
    {
        $this->assertTrue(false);
    }

    private function createTable()
    {
        $schemaManager = $this->getConnection()->getSchemaManager();
        $schema = $schemaManager->createSchema();

        $table = $this->aggregateAwareDBALEventStore->configureSchema(
            $schema
        );

        $schemaManager->createTable($table);
    }
}
