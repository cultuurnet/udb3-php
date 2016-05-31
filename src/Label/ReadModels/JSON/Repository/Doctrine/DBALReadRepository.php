<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
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
     * @param array $row
     * @return Entity
     */
    private function rowToEntity(array $row)
    {
        return new Entity(
            new UUID($row[SchemaConfigurator::UUID_COLUMN]),
            new StringLiteral($row[SchemaConfigurator::NAME_COLUMN]),
            $row[SchemaConfigurator::VISIBLE_COLUMN]
                ? Visibility::VISIBLE() : Visibility::INVISIBLE(),
            $row[SchemaConfigurator::PRIVATE_COLUMN]
                ? Privacy::PRIVACY_PRIVATE() : Privacy::PRIVACY_PUBLIC(),
            new UUID($row[SchemaConfigurator::PARENT_UUID_COLUMN]),
            new Natural($row[SchemaConfigurator::COUNT_COLUMN])
        );
    }
}
