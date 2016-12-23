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
    const UNIQUE_VALUE = 'unique';
    const OTHER_ID = 'otherId';
    const OTHER_UNIQUE_VALUE = 'otherUnique';

    /**
     * @var UniqueDBALEventStoreDecorator
     */
    private $uniqueDBALEventStoreDecorator;

    /**
     * @var DBALEventStore|\PHPUnit_Framework_MockObject_MockObject $dbalEventStore
     */
    private $dbalEventStore;

    /**
     * @var UniqueConstraintServiceInterface|\PHPUnit_Framework_MockObject_MockObject $uniqueConstraintService
     */
    private $uniqueConstraintService;

    /**
     * @var StringLiteral
     */
    private $uniqueTableName;

    protected function setUp()
    {
        $serializer = $this->createMock(SerializerInterface::class);

        $this->dbalEventStore = $this
            ->getMockBuilder(DBALEventStore::class)
            ->setConstructorArgs([$this->getConnection(), $serializer, $serializer, 'labelsEventStore'])
            ->enableProxyingToOriginalMethods()
            ->getMock();

        $this->uniqueTableName = new StringLiteral('uniqueTableName');

        $this->uniqueConstraintService = $this->createMock(UniqueConstraintServiceInterface::class);

        $this->uniqueConstraintService->expects($this->any())
            ->method('hasUniqueConstraint')
            ->willReturn(true);

        $this->uniqueConstraintService->expects($this->any())
            ->method('getUniqueConstraintValue')
            ->willReturn(new StringLiteral(self::UNIQUE_VALUE));

        $this->uniqueDBALEventStoreDecorator = new UniqueDBALEventStoreDecorator(
            $this->dbalEventStore,
            $this->connection,
            $this->uniqueTableName,
            $this->uniqueConstraintService
        );

        $schemaManager = $this->getConnection()->getSchemaManager();
        $schema = $schemaManager->createSchema();

        $table = $this
            ->uniqueDBALEventStoreDecorator
            ->configureSchema($schema);

        $schemaManager->createTable($table);
    }

    /**
     * @test
     */
    public function it_can_append_domain_messages_with_a_unique_value_if_the_unique_value_has_not_been_used_before()
    {
        $this->insert(self::OTHER_ID, self::OTHER_UNIQUE_VALUE);

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

        $this->assertEquals(self::UNIQUE_VALUE, $unique);
    }

    /**
     * @test
     */
    public function it_does_not_append_domain_messages_with_a_unique_value_if_the_unique_value_has_been_used_before()
    {
        $this->insert(self::OTHER_ID, self::UNIQUE_VALUE);

        $domainMessage = new DomainMessage(
            self::ID,
            0,
            new Metadata(),
            null,
            BroadwayDateTime::now()
        );

        try {
            $this->uniqueDBALEventStoreDecorator->append(
                $domainMessage->getId(),
                new DomainEventStream([$domainMessage])
            );
            $this->fail('Did not throw expected UniqueConstraintException.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(UniqueConstraintException::class, $e);
            $this->assertEquals(
                'Not unique: uuid = ' . self::ID . ', unique value = ' . self::UNIQUE_VALUE,
                $e->getMessage()
            );

            // Make sure no events were appended to the event store.
            $rowCountResult = $this->connection->createQueryBuilder()
                ->select('count(*) as total')
                ->from('labelsEventStore')
                ->execute()
                ->fetch();

            $rowCount = $rowCountResult['total'];

            $this->assertEquals(0, $rowCount);
        }
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
