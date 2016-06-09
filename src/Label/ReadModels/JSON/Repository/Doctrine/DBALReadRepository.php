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

        $this->getQueryBuilder()->select($aliases)
            ->from($this->getTableName()->toNative())
            ->where($whereId)
            ->setParameters([$uuid]);

        return $this->getResult($this->getQueryBuilder());
    }

    /**
     * @param StringLiteral $name
     * @return Entity|null
     */
    public function getByName(StringLiteral $name)
    {
        $aliases = $this->getAliases();
        $whereName = SchemaConfigurator::NAME_COLUMN . ' = ?';

        $this->getQueryBuilder()->select($aliases)
            ->from($this->getTableName()->toNative())
            ->where($whereName)
            ->setParameters([$name]);

        return $this->getResult($this->getQueryBuilder());
    }

    /**
     * @param Query $query
     * @return Entity[]|null
     */
    public function search(Query $query)
    {
        $aliases = $this->getAliases();

        $like = $this->getQueryBuilder()->expr()->like(
            SchemaConfigurator::NAME_COLUMN,
            ':' . SchemaConfigurator::NAME_COLUMN
        );

        $this->getQueryBuilder()->select($aliases)
            ->from($this->getTableName()->toNative())
            ->where($like)
            ->setParameter(
                SchemaConfigurator::NAME_COLUMN,
                '%' . $query->getValue()->toNative() . '%'
            );

        if ($query->getOffset()) {
            $this->getQueryBuilder()
                ->setFirstResult($query->getOffset()->toNative());
        }

        if ($query->getLimit()) {
            $this->getQueryBuilder()
                ->setMaxResults($query->getLimit()->toNative());
        }

        return $this->getResults($this->getQueryBuilder());
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
