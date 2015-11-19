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
     * @test
     */
    public function it_updates_the_organizer_linked_to_an_event_when_a_relation_already_exists()
    {
        $this->markTestIncomplete(
          'Not sure how to deal with this: SQLSTATE[HY000]: General error: 1 near "SET": syntax error'
        );

        $eventId = 'event-id';
        $organizerId = 'organizer-id';
        $expectedData[] = (object)[];

        $this->repository->storeOrganizer($eventId, $organizerId);
    }

    /**
     * @test
     */
    public function it_creates_a_new_organizer_relation_when_an_event_has_no_existing_relations()
    {
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}
