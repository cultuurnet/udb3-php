<?php

namespace CultuurNet\UDB3\Variations\ReadModel\Search\Doctrine;

use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use CultuurNet\UDB3\Variations\ReadModel\Search\Criteria;
use CultuurNet\UDB3\Variations\ReadModel\Search\RepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

class DBALRepository implements RepositoryInterface
{
    protected $tableName = 'event_variation_search_index';

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var ExpressionFactory
     */
    protected $expressionFactory;

    /**
     * @param Connection $connection
     */
    public function __construct(
        Connection $connection,
        ExpressionFactory $expressionFactory
    ) {
        $this->connection = $connection;
        $this->expressionFactory = $expressionFactory;
    }

    public function save(
        Id $variationId,
        Url $eventUrl,
        OwnerId $ownerId,
        Purpose $purpose
    ) {
        $this->connection->beginTransaction();

        $insert = $this->prepareInsertStatement();
        $insert->bindValue('id', (string) $variationId);
        $insert->bindValue('event', (string) $eventUrl);
        $insert->bindValue('owner', (string) $ownerId);
        $insert->bindValue('purpose', (string) $purpose);
        $insert->execute();

        $this->connection->commit();
    }

    private function prepareInsertStatement()
    {
        $table = $this->connection->quoteIdentifier($this->tableName);
        return $this->connection->prepare(
            "INSERT INTO {$table} SET
              id = :id,
              event = :event,
              owner = :owner,
              purpose = :purpose"
        );
    }

    public function getEventVariations(
        Criteria $criteria,
        $limit = 30,
        $offset = 0
    ) {
        $q = $this->connection->createQueryBuilder();
        $q
            ->select('id')
            ->from($this->tableName)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $conditions = $this->expressionFactory->createExpressionFromCriteria(
            $q->expr(),
            $criteria
        );

        if ($conditions) {
            $q->where($conditions);
        }

        $results = $q->execute();

        $ids = [];
        while ($variationId = $results->fetchColumn(0)) {
            $ids[] = $variationId;
        }

        return $ids;
    }

    public function remove(Id $variationId)
    {
        $this->connection->delete(
            $this->connection->quoteIdentifier($this->tableName),
            ['id' => (string)$variationId]
        );
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     * @return \Doctrine\DBAL\Schema\Table|null
     */
    public function configureSchema(Schema $schema)
    {
        if ($schema->hasTable($this->tableName)) {
            return null;
        }

        return $this->configureTable();
    }

    public function configureTable()
    {
        $schema = new Schema();

        $table = $schema->createTable($this->tableName);

        $table->addColumn(
            'id',
            'string',
            array('length' => 36, 'notnull' => true)
        );
        $table->addColumn(
            'event',
            'text',
            array('notnull' => true)
        );
        $table->addColumn(
            'owner',
            'string',
            array('length' => 36, 'notnull' => true)
        );
        $table->addColumn(
            'purpose',
            'text',
            array('length' => 36, 'notnull' => true)
        );

        $table->setPrimaryKey(array('id'));

        return $table;
    }
}
