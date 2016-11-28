<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\Doctrine\AbstractDBALRepository;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\LabelRelation;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;

class DBALReadRepository extends AbstractDBALRepository implements ReadRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function getLabelRelations(LabelName $labelName)
    {
        $aliases = $this->getAliases();
        $whereUuid = SchemaConfigurator::LABEL_NAME . ' = ?';

        $queryBuilder = $this->createQueryBuilder()->select($aliases)
            ->from($this->getTableName()->toNative())
            ->where($whereUuid)
            ->setParameters([$labelName->toNative()]);

        $statement = $queryBuilder->execute();

        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $labelRelation = LabelRelation::fromRelationalData($row);
            yield $labelRelation;
        }
    }

    /**
     * @return array
     */
    private function getAliases()
    {
        return [
            SchemaConfigurator::LABEL_NAME,
            SchemaConfigurator::RELATION_TYPE,
            SchemaConfigurator::RELATION_ID
        ];
    }
}
