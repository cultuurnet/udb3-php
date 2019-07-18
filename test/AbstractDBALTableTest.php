<?php

namespace CultuurNet\UDB3;

use PDO;
use PHPUnit\Framework\TestCase;

abstract class AbstractDBALTableTest extends TestCase
{
    use DBALTestConnectionTrait;
    
    /**
     * @var StringLiteral
     */
    protected $tableName;

    /**
     * @param array $rows
     */
    protected function insert(array $rows)
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

    protected function assertCurrentData(array $expectedData)
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
     * @return array
     */
    protected function loadData($filePath)
    {
        return json_decode(
            file_get_contents($filePath)
        );
    }
}
