<?php

namespace CultuurNet\UDB3\EventSourcing\DBAL;

use Broadway\Domain\DomainEventStreamInterface;
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\DBALEventStore;
use CultuurNet\UDB3\EventSourcing\AbstractEventStoreDecorator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use ValueObjects\String\String as StringLiteral;

class UniqueDBALEventStoreDecorator extends AbstractEventStoreDecorator
{
    const UUID_COLUMN = 'uuid_col';
    const UNIQUE_COLUMN = 'unique_col';

    /**
     * @var DBALEventStore
     */
    private $dbalEventStore;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var StringLiteral
     */
    private $uniqueTableName;

    /**
     * @var UniqueHelperInterface
     */
    private $uniqueHelper;

    /**
     * UniqueNameDBALEventStoreDecorator constructor.
     * @param DBALEventStore $dbalEventStore
     * @param Connection $connection
     * @param StringLiteral $uniqueTableName
     * @param UniqueHelperInterface $uniqueHelper
     */
    public function __construct(
        DBALEventStore $dbalEventStore,
        Connection $connection,
        StringLiteral $uniqueTableName,
        UniqueHelperInterface $uniqueHelper
    ) {
        parent::__construct($dbalEventStore);

        $this->dbalEventStore = $dbalEventStore;
        $this->connection = $connection;
        $this->uniqueTableName = $uniqueTableName;
        $this->uniqueHelper = $uniqueHelper;
    }

    /**
     * @inheritdoc
     * @throws UniqueConstraintException
     */
    public function append($id, DomainEventStreamInterface $eventStream)
    {
        $this->connection->beginTransaction();

        try {
            // First make sure that the events itself can be stored,
            // then check the uniqueness. 
            parent::append($id, $eventStream);

            foreach ($eventStream as $domainMessage) {
                $this->processDomainMessage(
                    $this->uniqueHelper,
                    $domainMessage
                );
            }

            $this->connection->commit();
        } catch (\Exception $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    /**
     * @inheritdoc
     */
    public function configureSchema(Schema $schema)
    {
        // Ensure that when creating the normal event store table the extra
        // table, that ensures uniqueness of the entity, also gets created.
        if (!$schema->hasTable($this->uniqueTableName->toNative())) {
            $this->createUniqueTable($this->connection, $this->uniqueTableName);
        }

        return $this->dbalEventStore->configureSchema($schema);
    }

    /**
     * @param Connection $connection
     * @param StringLiteral $tableName
     */
    private function createUniqueTable(
        Connection $connection,
        StringLiteral $tableName
    ) {
        $schemaManager = $connection->getSchemaManager();
        $schema = $schemaManager->createSchema();

        $table = $schema->createTable($tableName->toNative());

        $table->addColumn(self::UUID_COLUMN, Type::GUID)
            ->setLength(36)
            ->setNotnull(true);

        $table->addColumn(self::UNIQUE_COLUMN, Type::STRING)
            ->setLength(256)
            ->setNotnull(true);

        $table->setPrimaryKey([self::UUID_COLUMN]);
        $table->addUniqueIndex([self::UUID_COLUMN]);
        $table->addUniqueIndex([self::UNIQUE_COLUMN]);

        $schemaManager->createTable($table);
    }

    /**
     * @param UniqueHelperInterface $uniqueHelper
     * @param DomainMessage $domainMessage
     * @throws UniqueConstraintException
     */
    private function processDomainMessage(
        UniqueHelperInterface $uniqueHelper,
        DomainMessage $domainMessage
    ) {
        if ($uniqueHelper->requiresUnique($domainMessage)) {

            $unique = $uniqueHelper->getUnique($domainMessage);

            if ($this->isUnique($unique->toNative())) {
                $this->insertUuidAndUnique(
                    $domainMessage->getId(),
                    $unique->toNative());
            } else {
                throw new UniqueConstraintException(
                    $domainMessage->getId(),
                    $unique
                );
            }
        }
    }

    /**
     * @param string $unique
     * @return bool
     */
    private function isUnique($unique)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select(self::UUID_COLUMN)
            ->from($this->uniqueTableName->toNative())
            ->where(self::UNIQUE_COLUMN . ' = ?')
            ->setParameters([$unique]);

        $statement = $queryBuilder->execute();
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return (count($rows) < 1);
    }

    /**
     * @param string $uuid
     * @param string $unique
     */
    private function insertUuidAndUnique($uuid, $unique)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->insert($this->uniqueTableName)
            ->values([
                self::UUID_COLUMN => '?',
                self::UNIQUE_COLUMN => '?'
            ])
            ->setParameters([
                $uuid,
                $unique
            ]);

        $queryBuilder->execute();
    }
}
