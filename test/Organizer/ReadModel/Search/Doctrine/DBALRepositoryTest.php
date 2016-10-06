<?php

namespace CultuurNet\UDB3\Organizer\ReadModel\Search\Doctrine;

use CultuurNet\UDB3\DBALTestConnectionTrait;
use CultuurNet\UDB3\Organizer\ReadModel\Search\Query;
use CultuurNet\UDB3\Organizer\ReadModel\Search\Results;
use PHPUnit_Framework_TestCase;
use ValueObjects\Number\Natural;
use ValueObjects\Web\Url;
use ValueObjects\String\String as StringLiteral;

class DBALRepositoryTest extends PHPUnit_Framework_TestCase
{
    use DBALTestConnectionTrait;

    /**
     * @var DBALRepository
     */
    private $repository;

    /**
     * @var StringLiteral
     */
    protected $tableName;

    /**
     * @var array
     */
    protected $data;

    /**
     * @return array
     */
    private function loadData()
    {
        return json_decode(file_get_contents(__DIR__ . '/initial-values.json'));
    }

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
    public function it_should_find_an_organizer_by_website()
    {
        $website = 'http://ac.me';
        $query = new Query(
            Natural::fromNative(0),
            Natural::fromNative(10),
            Url::fromNative($website),
            null
        );

        $expectedResult = new Results(
            Natural::fromNative('10'),
            [
                [
                    'uuid' => 'd70e2034-3316-4d2a-ac0f-4692824cbf34',
                    'title' => 'ACME CORP.',
                    'website' => 'http://ac.me',
                ]
            ],
            Natural::fromNative('1')
        );
        $result = $this->repository->search($query);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @test
     */
    public function it_should_find_an_organizer_by_name()
    {
        $website = 'http://du.de';
        $query = new Query(
            Natural::fromNative(0),
            Natural::fromNative(10),
            null,
            new StringLiteral('Foo')
        );

        $expectedResult = new Results(
            Natural::fromNative('10'),
            [
                [
                    'uuid' => 'ad9eaf61-1b91-4dd8-877e-54b357059b14',
                    'title' => 'Foo Barbers',
                    'website' => 'http://foo.bar',
                ]
            ],
            Natural::fromNative('1')
        );
        $result = $this->repository->search($query);

        $this->assertEquals($expectedResult, $result);
    }
}
