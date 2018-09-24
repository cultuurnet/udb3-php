<?php

namespace CultuurNet\UDB3\MyOrganizers\ReadModel\Doctrine;

use DateTimeInterface;
use CultuurNet\UDB3\MyOrganizers\ReadModel\RepositoryInterface;
use Doctrine\DBAL\Connection;
use ValueObjects\StringLiteral\StringLiteral;

class DBALRepository implements RepositoryInterface
{
    use DBALHelperTrait;

    /** @var StringLiteral */
    private $tableName;

    /**
     * @param Connection $connection
     * @param StringLiteral $tableName
     */
    public function __construct(
        Connection $connection,
        StringLiteral $tableName
    ) {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    public function add(
        string $id,
        string $userId,
        DateTimeInterface $created
    ) {
        $this->connection->beginTransaction();

        try {
            /** @var \Doctrine\DBAL\Query\QueryBuilder $q */
            $q = $this->connection->createQueryBuilder();
            $q->insert($this->tableName->toNative())
                ->values(
                    [
                        Columns::ID => $this->parameter(Columns::ID),
                        Columns::UID => $this->parameter(Columns::UID),
                        Columns::CREATED => $this->parameter(Columns::CREATED),
                        // We intentionally set updated the same as created.
                        Columns::UPDATED => $this->parameter(Columns::CREATED),
                    ]
                );

            $q->setParameter(Columns::ID, $id);
            $q->setParameter(Columns::UID, $userId);
            $q->setParameter(Columns::CREATED, $created->getTimestamp());

            $q->execute();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $this->connection->commit();
    }

    public function delete(string $id)
    {
        $q = $this->connection->createQueryBuilder();

        $q->delete($this->tableName->toNative())
            ->where($this->matchesId())
            ->setParameter(Columns::ID, $id)
            ->execute();
    }

    public function setUpdateDate(string $id, DateTimeInterface $updated)
    {
        $q = $this->connection->createQueryBuilder();

        $q->update($this->tableName->toNative())
            ->where($this->matchesId())
            ->set(Columns::UPDATED, $updated->getTimestamp())
            ->setParameter(Columns::ID, $id)
            ->execute();
    }

    private function matchesId(): string
    {
        $expr = $this->connection->getExpressionBuilder();

        return $expr->eq(Columns::ID, $this->parameter(Columns::ID));
    }
}
