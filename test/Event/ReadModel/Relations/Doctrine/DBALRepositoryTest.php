<?php

namespace CultuurNet\UDB3\Event\ReadModel\Relations\Doctrine;

use CultuurNet\UDB3\DBALTestConnectionTrait;
use PDO;

class DBALRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use DBALTestConnectionTrait;

    /**
     * @var DBALRepository
     */
    private $repository;

    /**
     * @var string
     */
    private $tableName;

    public function setUp()
    {
        $this->repository = new DBALRepository(
            $this->getConnection()
        );

        $schemaManager = $this->getConnection()->getSchemaManager();
        $schema = $schemaManager->createSchema();

        $schemaManager->createTable(
            $this->repository->configureSchema($schema)
        );

        $this->tableName = 'event_relations';
    }

    /**
     * @param array $expectedData
     * @param string $tableName
     */
    private function assertTableData($expectedData, $tableName)
    {
        $expectedData = array_values($expectedData);

        $results = $this->getConnection()->executeQuery('SELECT * from ' . $tableName);

        $actualData = $results->fetchAll(PDO::FETCH_OBJ);

        $this->assertEquals(
            $expectedData,
            $actualData
        );
    }

    /**
     * @param string $tableName
     * @param array $rows
     */
    private function insertTableData($tableName, $rows)
    {
        $q = $this->getConnection()->createQueryBuilder();

        $schema = $this->getConnection()->getSchemaManager()->createSchema();

        $columns = $schema
            ->getTable($tableName)
            ->getColumns();

        $values = [];
        foreach ($columns as $column) {
            $values[$column->getName()] = '?';
        }

        $q->insert($tableName)
            ->values($values);

        foreach ($rows as $row) {
            $parameters = [];
            foreach (array_keys($values) as $columnName) {
                $parameters[] = $row->$columnName;
            }

            $q->setParameters($parameters);

            $q->execute();
        }
    }

    /**
     * @test
     */
    public function it_updates_the_organizer_linked_to_an_event_when_a_relation_already_exists()
    {
        $existingData[] = (object)[
            'event' => 'event-id',
            'organizer' => 'old-organizer-id',
            'place' => 'some-place-id'
        ];
        $this->insertTableData($this->tableName, $existingData);
        $eventId = 'event-id';
        $organizerId = 'new-organizer-id';
        $expectedData[] = (object)[
            'event' => 'event-id',
            'organizer' => 'new-organizer-id',
            'place' => 'some-place-id'
        ];

        $this->repository->storeOrganizer($eventId, $organizerId);

        $this->assertTableData($expectedData, $this->tableName);
    }

    /**
     * @test
     */
    public function it_creates_a_new_organizer_relation_when_an_event_has_no_existing_relations()
    {
        $eventId = 'event-id';
        $organizerId = 'organizer-id';
        $expectedData[] = (object)[
            'event' => 'event-id',
            'organizer' => 'organizer-id',
            'place' => null
        ];

        $this->repository->storeOrganizer($eventId, $organizerId);

        $this->assertTableData($expectedData, $this->tableName);
    }

    /**
     * @test
     */
    public function it_updates_existing_relations_when_removing_an_event_organizer()
    {
        $existingData[] = (object)[
            'event' => 'event-id',
            'organizer' => 'organizer-id',
            'place' => 'some-place-id'
        ];
        $this->insertTableData($this->tableName, $existingData);
        $eventId = 'event-id';
        $expectedData[] = (object)[
            'event' => 'event-id',
            'organizer' => null,
            'place' => 'some-place-id'
        ];

        $this->repository->removeOrganizer($eventId);

        $this->assertTableData($expectedData, $this->tableName);
    }
}
