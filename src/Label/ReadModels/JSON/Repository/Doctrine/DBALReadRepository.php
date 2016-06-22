<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\Doctrine\AbstractDBALRepository;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Query;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use Doctrine\DBAL\Query\QueryBuilder;
use ValueObjects\Identity\UUID;
use ValueObjects\Number\Natural;
use ValueObjects\String\String as StringLiteral;

class DBALReadRepository extends AbstractDBALRepository implements ReadRepositoryInterface
{
    /**
     * @param UUID $uuid
     * @return Entity|null
     */
    public function getByUuid(UUID $uuid)
    {
        $aliases = $this->getAliases();
        $whereId = SchemaConfigurator::UUID_COLUMN . ' = ?';

        $queryBuilder = $this->createQueryBuilder()->select($aliases)
            ->from($this->getTableName()->toNative())
            ->where($whereId)
            ->setParameters([$uuid]);

        return $this->getResult($queryBuilder);
    }

    /**
     * @param StringLiteral $name
     * @return Entity|null
     */
    public function getByName(StringLiteral $name)
    {
        $aliases = $this->getAliases();
        $whereName = SchemaConfigurator::NAME_COLUMN . ' = ?';

        $queryBuilder = $this->createQueryBuilder()->select($aliases)
            ->from($this->getTableName()->toNative())
            ->where($whereName)
            ->setParameters([$name]);

        return $this->getResult($queryBuilder);
    }

    /**
     * @param Query $query
     * @return Entity[]|null
     */
    public function search(Query $query)
    {
        $aliases = $this->getAliases();

        $queryBuilder = $this->createQueryBuilder();

        $like = $this->createLike($queryBuilder);

        $queryBuilder->select($aliases)
            ->from($this->getTableName()->toNative())
            ->where($like)
            ->setParameter(
                SchemaConfigurator::NAME_COLUMN,
                $this->createLikeParameter($query)
            )
            ->orderBy(SchemaConfigurator::NAME_COLUMN);

        if ($query->getOffset()) {
            $queryBuilder
                ->setFirstResult($query->getOffset()->toNative());
        }

        if ($query->getLimit()) {
            $queryBuilder
                ->setMaxResults($query->getLimit()->toNative());
        }

        return $this->getResults($queryBuilder);
    }

    /**
     * @param Query $query
     * @return Natural
     */
    public function searchTotalLabels(Query $query)
    {
        $queryBuilder = $this->createQueryBuilder();
        $like = $this->createLike($queryBuilder);

        $queryBuilder->select('COUNT(*)')
            ->from($this->getTableName()->toNative())
            ->where($like)
            ->setParameter(
                SchemaConfigurator::NAME_COLUMN,
                $this->createLikeParameter($query)
            );

        $statement = $queryBuilder->execute();
        $countArray = $statement->fetch(\PDO::FETCH_NUM);

        return new Natural($countArray[0]);
    }

    /**
     * @return array
     */
    private function getAliases()
    {
        return [
            SchemaConfigurator::UUID_COLUMN,
            SchemaConfigurator::NAME_COLUMN,
            SchemaConfigurator::VISIBLE_COLUMN,
            SchemaConfigurator::PRIVATE_COLUMN,
            SchemaConfigurator::PARENT_UUID_COLUMN,
            SchemaConfigurator::COUNT_COLUMN
        ];
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return string
     */
    private function createLike(QueryBuilder $queryBuilder)
    {
        return $queryBuilder->expr()->like(
            SchemaConfigurator::NAME_COLUMN,
            ':' . SchemaConfigurator::NAME_COLUMN
        );
    }

    /**
     * @param Query $query
     * @return string
     */
    private function createLikeParameter(Query $query)
    {
        return '%' . $query->getValue()->toNative() . '%';
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return Entity|null
     */
    private function getResult(QueryBuilder $queryBuilder)
    {
        $entity = null;

        $statement = $queryBuilder->execute();
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            $entity = $this->rowToEntity($row);
        }

        return $entity;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return Entity[]|null
     */
    private function getResults(QueryBuilder $queryBuilder)
    {
        $entities = null;

        $statement = $queryBuilder->execute();
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $entities[] = $this->rowToEntity($row);
        }

        return $entities;
    }

    /**
     * @param array $row
     * @return Entity
     */
    private function rowToEntity(array $row)
    {
        $uuid = new UUID($row[SchemaConfigurator::UUID_COLUMN]);

        $name = new StringLiteral($row[SchemaConfigurator::NAME_COLUMN]);

        $visibility = $row[SchemaConfigurator::VISIBLE_COLUMN]
            ? Visibility::VISIBLE() : Visibility::INVISIBLE();

        $privacy = $row[SchemaConfigurator::PRIVATE_COLUMN]
            ? Privacy::PRIVACY_PRIVATE() : Privacy::PRIVACY_PUBLIC();

        $parentUuid = new UUID($row[SchemaConfigurator::PARENT_UUID_COLUMN]);

        $count = new Natural($row[SchemaConfigurator::COUNT_COLUMN]);

        return new Entity(
            $uuid,
            $name,
            $visibility,
            $privacy,
            $parentUuid,
            $count
        );
    }
}
