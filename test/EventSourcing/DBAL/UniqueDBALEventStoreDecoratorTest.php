<?php

namespace CultuurNet\UDB3\EventSourcing\DBAL;

use Broadway\Domain\DateTime as BroadwayDateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventStore\DBALEventStore;
use Broadway\Serializer\SerializerInterface;
use CultuurNet\UDB3\DBALTestConnectionTrait;
use ValueObjects\String\String as StringLiteral;

class UniqueDBALEventStoreDecoratorTest extends \PHPUnit_Framework_TestCase
{
    use DBALTestConnectionTrait;

    const ID = 'id';
    const UNIQUE = 'unique';
    const OTHER_ID = 'otherId';
    const OTHER_UNIQUE = 'otherUnique';

    /**
     * @var UniqueDBALEventStoreDecorator
     */
    private $uniqueDBALEventStoreDecorator;

    /**
     * @var DBALEventStore|\PHPUnit_Framework_MockObject_MockObject $dbalEventStore
     */
    private $dbalEventStore;

    /**
     * @var UniqueHelperInterface|\PHPUnit_Framework_MockObject_MockObject $uniqueHelper
     */
    private $uniqueHelper;

    /**
     * @var StringLiteral
     */
    private $uniqueTableName;

    protected function setUp()
    {
        $serializer = $this->getMock(SerializerInterface::class);

        $this->dbalEventStore = $this->getMock(
            DBALEventStore::class,
            null,
            [$this->getConnection(), $serializer, $serializer, 'labelsEventStore']
        );

        $this->uniqueTableName = new StringLiteral('uniqueTableName');

        $this->uniqueHelper = $this->getMock(UniqueHelperInterface::class);
        $this->mockRequiresUnique();
        $this->mockGetUnique();

        $this->uniqueDBALEventStoreDecorator = new UniqueDBALEventStoreDecorator(
            $this->dbalEventStore,
            $this->connection,
            $this->uniqueTableName,
            $this->uniqueHelper
        );

        $schemaManager = $this->getConnection()->getSchemaManager();
        $schema = $schemaManager->createSchema();

        $table = $this->uniqueDBALEventStoreDecorator->configureSchema(
            $schema
        );

        $schemaManager->createTable($table);
    }

    /**
     * @test
     */
    public function it_can_append_unique()
    {
        $this->insert(self::OTHER_ID, self::OTHER_UNIQUE);

        $domainMessage = new DomainMessage(
            self::ID,
            0,
            new Metadata(),
            null,
            BroadwayDateTime::now()
        );

        $this->uniqueDBALEventStoreDecorator->append(
            $domainMessage->getId(),
            new DomainEventStream([$domainMessage])
        );

        $unique = $this->select(self::ID);

        $this->assertEquals(self::UNIQUE, $unique);
    }

    /**
     * @test
     */
    public function it_does_not_append_non_unique()
    {
        $this->insert(self::OTHER_ID, self::UNIQUE);

        $domainMessage = new DomainMessage(
            self::ID,
            0,
            new Metadata(),
            null,
            BroadwayDateTime::now()
        );

        $this->setExpectedException(
            UniqueConstraintException::class,
            'Not unique: uuid = ' . self::ID . ', unique value = ' . self::UNIQUE
        );

        $this->uniqueDBALEventStoreDecorator->append(
            $domainMessage->getId(),
            new DomainEventStream([$domainMessage])
        );
    }

    private function mockRequiresUnique()
    {
        $this->uniqueHelper->method('requiresUnique')
            ->willReturn(true);
    }

    private function mockGetUnique()
    {
        $this->uniqueHelper->method('getUnique')
            ->willReturn(new StringLiteral(self::UNIQUE));
    }

    /**
     * @param string $uuid
     * @param string $unique
     */
    private function insert($uuid, $unique)
    {
        $sql = 'INSERT INTO ' . $this->uniqueTableName . ' VALUES (?, ?)';

        $this->connection->executeQuery($sql, [$uuid, $unique]);
    }

    /**
     * @param string $uuid
     * @returns string
     * @throws \Doctrine\DBAL\DBALException
     */
    private function select($uuid)
    {
        $tableName = $this->uniqueTableName;
        $where = ' WHERE ' . UniqueDBALEventStoreDecorator::UUID_COLUMN . ' = ?';

        $sql = 'SELECT * FROM ' . $tableName . $where;

        $statement = $this->connection->executeQuery($sql, [$uuid]);
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $rows[0][UniqueDBALEventStoreDecorator::UNIQUE_COLUMN];
    }
}
