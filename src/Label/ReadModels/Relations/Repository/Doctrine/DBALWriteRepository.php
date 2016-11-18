<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\Doctrine\AbstractDBALRepository;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\WriteRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use ValueObjects\String\String as StringLiteral;

class DBALWriteRepository extends AbstractDBALRepository implements WriteRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function save(
        LabelName $labelName,
        RelationType $relationType,
        StringLiteral $relationId
    ) {
        $queryBuilder = $this->createQueryBuilder()
            ->insert($this->getTableName())
            ->values([
                SchemaConfigurator::LABEL_NAME => '?',
                SchemaConfigurator::RELATION_TYPE => '?',
                SchemaConfigurator::RELATION_ID => '?'
            ])
            ->setParameters([
                $labelName->toNative(),
                $relationType->toNative(),
                $relationId->toNative()
            ]);

        $queryBuilder->execute();
    }

    /**
     * @inheritdoc
     */
    public function deleteByLabelNameAndRelationId(
        LabelName $labelName,
        StringLiteral $relationId
    ) {
        $queryBuilder = $this->createQueryBuilder()
            ->delete($this->getTableName())
            ->where(SchemaConfigurator::LABEL_NAME . ' = ?')
            ->andWhere(SchemaConfigurator::RELATION_ID . ' = ?')
            ->setParameters([$labelName->toNative(), $relationId->toNative()]);

        $queryBuilder->execute();
    }
}
