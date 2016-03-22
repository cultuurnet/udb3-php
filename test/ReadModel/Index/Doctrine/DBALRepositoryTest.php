<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\ReadModel\Index\Doctrine;

use CultuurNet\UDB3\DBALTestConnectionTrait;
use CultuurNet\UDB3\ReadModel\Index\EntityType;
use PHPUnit_Framework_TestCase;
use PDO;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\Domain;

class DBALRepositoryTest extends PHPUnit_Framework_TestCase
{
    use DBALTestConnectionTrait;

    /**
     * @var DBALRepository
     */
    protected $repository;

    /**
     * @var StringLiteral
     */
    protected $tableName;

    /**
     * @var array
     */
    protected $data;

    public function setUp()
    {
        $this->tableName = new StringLiteral('testtable');

        $schemaManager = $this->getConnection()->getSchemaManager();

        (new SchemaConfigurator($this->tableName))
            ->configure($schemaManager);

        $this->data = $this->loadData();

        $this->insert($this->data);

        $this->repository = new DBALRepository(
            $this->getConnection(),
            $this->tableName
        );
    }

    /**
     * @return array
     */
    private function loadData()
    {
         return json_decode(file_get_contents(__DIR__ . '/initial-values.json'));
    }

    /**
     * @param array $rows
     */
    private function insert($rows)
    {
        $q = $this->getConnection()->createQueryBuilder();

        $schema = $this->getConnection()->getSchemaManager()->createSchema();

        $columns = $schema
            ->getTable($this->tableName->toNative())
            ->getColumns();

        $values = [];
        foreach ($columns as $column) {
            $values[$column->getName()] = '?';
        }

        $q->insert($this->tableName->toNative())
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
    public function it_updates_existing_data_by_unique_combination_of_id_and_entity_type()
    {
        $this->repository->updateIndex(
            'abc',
            EntityType::ORGANIZER(),
            'bar',
            'Test organizer abc update',
            '3020',
            Domain::specifyType('udb.be'),
            new \DateTimeImmutable('@100')
        );

        $expectedData = $this->data;

        $expectedData[3] = [
            'uid' => 'bar',
            'title' => 'Test organizer abc update',
            'created' => '100',
            'zip' => '3020',
        ] + (array) $expectedData[3];

        $expectedData[3] = (object) $expectedData[3];

        $this->assertCurrentData($expectedData);
    }

    /**
     * @test
     */
    public function it_inserts_new_unique_combinations_of_id_and_entity_type()
    {
        $this->repository->updateIndex(
            'xyz',
            EntityType::EVENT(),
            'foo',
            'Test event xyz',
            '3020',
            Domain::specifyType('udb.be'),
            new \DateTimeImmutable('@0')
        );

        $expectedData = $this->data;

        $expectedData[] = (object)[
            'entity_id' => 'xyz',
            'entity_type' => 'event',
            'uid' => 'foo',
            'title' => 'Test event xyz',
            'zip' => '3020',
            'created' => 0,
            'updated' => 0,
            'owning_domain' => 'udb.be'
        ];

        $this->assertCurrentData($expectedData);
    }

    private function assertCurrentData($expectedData)
    {
        $expectedData = array_values($expectedData);

        $results = $this->getConnection()->executeQuery('SELECT * from ' . $this->tableName->toNative());

        $actualData = $results->fetchAll(PDO::FETCH_OBJ);

        $this->assertEquals(
            $expectedData,
            $actualData
        );
    }

    /**
     * @test
     */
    public function it_deletes_by_unique_combination_of_id_and_entity_type()
    {
        $this->repository->deleteIndex('abc', EntityType::PLACE());

        $expectedData = $this->data;

        unset($expectedData[0]);

        $this->assertCurrentData($expectedData);

        $this->repository->deleteIndex('abc', EntityType::ORGANIZER());

        unset($expectedData[3]);

        $this->assertCurrentData($expectedData);
    }

    /**
     * @test
     */
    public function it_can_find_places_by_postal_code()
    {
        $expectedIds = [
            'abc',
            '123'
        ];

        $this->assertEquals(
            $expectedIds,
            $this->repository->findPlacesByPostalCode('3000')
        );
    }

    /**
     * @test
     */
    public function it_should_update_the_updated_column_when_setting_the_updated_date()
    {
        $itemId = 'def';
        $dateUpdated = new \DateTime();
        $dateUpdated->setTimestamp(1171502725);

        $expectedData = $this->data;

        $expectedData[1] = [
                'updated' => 1171502725,
            ] + (array) $expectedData[1];

        $expectedData[1] = (object) $expectedData[1];

        $this->repository->setUpdateDate($itemId, $dateUpdated);

        $this->assertCurrentData($expectedData);
    }
}
