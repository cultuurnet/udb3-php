<?php

namespace CultuurNet\UDB3\Variations\ReadModel\Relations\Doctrine;

use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use CultuurNet\UDB3\Variations\ReadModel\Relations\RepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

class DBALRepository implements RepositoryInterface
{
    protected $tableName = 'event_variation_relations';

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function storeRelations(
        Id $variationId,
        Url $eventUrl,
        OwnerId $ownerId,
        Purpose $purpose
    ) {
        $this->connection->beginTransaction();

        $insert = $this->prepareInsertStatement();
        $insert->bindValue('event', $eventUrl);
        $insert->bindValue('variation', $variationId);
        $insert->bindValue('owner', $ownerId);
        $insert->bindValue('purpose', (string) $purpose);
        $insert->execute();

        $this->connection->commit();
    }

    private function prepareInsertStatement()
    {
        $table = $this->connection->quoteIdentifier($this->tableName);
        return $this->connection->prepare(
            "INSERT INTO {$table} SET
              event = :event,
              variation = :variation,
              owner = :owner,
              purpose = :purpose
            ON DUPLICATE KEY UPDATE
              place = :place,
              organizer = :organizer"
        );
    }

    public function getOwnerEventVariationByPurpose(
        Url $eventUrl,
        OwnerId $ownerId,
        Purpose $purpose
    ) {
        $q = $this->connection->createQueryBuilder();
        $q
            ->select('variation')
            ->from($this->tableName)
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('event', $eventUrl),
                    $q->expr()->eq('owner', $ownerId),
                    $q->expr()->eq('purpose', (string) $purpose)
                )
            );
        $results = $q->execute();

        return $results->fetchColumn(0);
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
            'variation',
            'string',
            array('length' => 36, 'notnull' => false)
        );
        $table->addColumn(
            'event',
            'string',
            array('length' => 36, 'notnull' => false)
        );
        $table->addColumn(
            'owner',
            'string',
            array('length' => 36, 'notnull' => false)
        );
        $table->addColumn(
            'purpose',
            'string',
            array('length' => 36, 'notnull' => false)
        );

        $table->setPrimaryKey(array('variation'));

        return $table;
    }
}
