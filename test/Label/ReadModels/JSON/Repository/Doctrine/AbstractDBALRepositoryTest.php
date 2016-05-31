<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use ValueObjects\String\String as StringLiteral;

class AbstractDBALRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var StringLiteral
     */
    private $tableName;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var AbstractDBALRepository
     */
    private $abstractDBALRepository;

    protected function setUp()
    {
        $this->connection = DriverManager::getConnection(
            [
                'url' => 'sqlite:///:memory:',
            ]
        );

        $this->tableName = new StringLiteral('tableName');

        $this->queryBuilder = $this->connection->createQueryBuilder();

        $this->abstractDBALRepository = $this->getMockForAbstractClass(
            AbstractDBALRepository::class,
            [
                $this->connection,
                $this->tableName,
                $this->queryBuilder
            ]
        );
    }

    /**
     * @test
     */
    public function it_stores_a_connection()
    {
        $this->assertEquals(
            $this->connection,
            $this->abstractDBALRepository->getConnection()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_table_name()
    {
        $this->assertEquals(
            $this->tableName,
            $this->abstractDBALRepository->getTableName()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_query_builder()
    {
        $this->assertEquals(
            $this->queryBuilder,
            $this->abstractDBALRepository->getQueryBuilder()
        );
    }
}
