<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\ReadModel\Permission\Doctrine;

use Doctrine\DBAL\DriverManager;
use ValueObjects\String\String;
use PDO;

class DBALRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DBALRepository
     */
    private $repository;

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

        $table = new String('event_permission');

        (new SchemaConfigurator($table))->configure(
            $connection->getSchemaManager()
        );

        $this->repository = new DBALRepository(
            $table,
            $connection
        );
    }

    /**
     * @test
     */
    public function it_can_add_and_query_event_permissions()
    {
        $johnDoe = new String('abc');
        $editableByJohnDoe = [
            new String('123'),
            new String('456'),
            new String('789'),
        ];
        $janeDoe = new String('def');
        $editableByJaneDoe = [
            new String('101112'),
            new String('131415'),
            new String('456'),
        ];

        $this->assertEquals(
            [],
            $this->repository->getEditableEvents($johnDoe)
        );

        $this->assertEquals(
            [],
            $this->repository->getEditableEvents($janeDoe)
        );

        array_walk($editableByJohnDoe, [$this, 'markEditable'], $johnDoe);
        array_walk($editableByJaneDoe, [$this, 'markEditable'], $janeDoe);

        $this->assertEquals(
            $editableByJohnDoe,
            $this->repository->getEditableEvents($johnDoe)
        );

        $this->assertEquals(
            $editableByJaneDoe,
            $this->repository->getEditableEvents($janeDoe)
        );
    }

    /**
     * @param String $eventId
     * @param string $key
     * @param String $userId
     */
    private function markEditable(String $eventId, $key, String $userId)
    {
        $this->repository->markEventEditableByUser($eventId, $userId);
    }

    /**
     * @test
     */
    public function it_silently_ignores_adding_duplicate_permissions()
    {
        $johnDoe = new String('abc');
        $editableByJohnDoe = [
            new String('123'),
            new String('456'),
            new String('789'),
        ];

        array_walk($editableByJohnDoe, [$this, 'markEditable'], $johnDoe);

        $this->repository->markEventEditableByUser(new String('456'), $johnDoe);

        $this->assertEquals(
            $editableByJohnDoe,
            $this->repository->getEditableEvents($johnDoe)
        );
    }
}
