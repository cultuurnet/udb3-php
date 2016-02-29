<?php

namespace CultuurNet\UDB3\Variations\ReadModel\Search\Doctrine;

use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OfferType;
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
     * @param ExpressionFactory $expressionFactory
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
        Purpose $purpose,
        OfferType $type
    ) {
        $this->connection->beginTransaction();

        $this->connection->insert(
            $this->connection->quoteIdentifier($this->tableName),
            [
                'id' => (string) $variationId,
                'owner' => (string) $ownerId,
                'purpose' => (string) $purpose,
                'inserted' => time(),
                'offer' => (string) $eventUrl,
                'type' => (string) $type,
            ]
        );

        $this->connection->commit();
    }

    /**
     * @inheritdoc
     */
    public function countOfferVariations(Criteria $criteria)
    {
        $q = $this->connection->createQueryBuilder();
        $q
            ->select('COUNT(id) as total')
            ->from($this->tableName);

        $conditions = $this->expressionFactory->createExpressionFromCriteria(
            $q->expr(),
            $criteria
        );

        if ($conditions) {
            $q->where($conditions);
        }

        return intval($q->execute()->fetchColumn(0));
    }

    /**
     * @inheritdoc
     */
    public function getOfferVariations(
        Criteria $criteria,
        $limit = 30,
        $page = 0
    ) {
        $offset = $limit * $page;
        $q = $this->connection->createQueryBuilder();
        $q
            ->select('id')
            ->from($this->tableName)
            ->orderBy('inserted')
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
            array('notnull' => true)
        );

        $table->addColumn(
            'inserted',
            'integer',
            array('notnull' => true, 'unsigned' => true)
        );

        $table->setPrimaryKey(array('id'));

        $table->addIndex(['inserted']);

        return $table;
    }
}
