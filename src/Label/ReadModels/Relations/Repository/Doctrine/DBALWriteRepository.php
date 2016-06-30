<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\Doctrine\AbstractDBALRepository;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\WriteRepositoryInterface;
use CultuurNet\UDB3\Offer\OfferType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class DBALWriteRepository extends AbstractDBALRepository implements WriteRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function save(
        UUID $uuid,
        StringLiteral $labelName,
        OfferType $relationType,
        StringLiteral $relationId
    ) {
        $queryBuilder = $this->createQueryBuilder()
            ->insert($this->getTableName())
            ->values([
                SchemaConfigurator::UUID_COLUMN => '?',
                SchemaConfigurator::LABEL_NAME_COLUMN => '?',
                SchemaConfigurator::RELATION_TYPE_COLUMN => '?',
                SchemaConfigurator::RELATION_ID_COLUMN => '?'
            ])
            ->setParameters([
                $uuid->toNative(),
                (string) $labelName,
                $relationType->toNative(),
                $relationId->toNative()
            ]);

        $queryBuilder->execute();
    }

    /**
     * @inheritdoc
     */
    public function deleteByUuidAndRelationId(
        UUID $uuid,
        StringLiteral $offerUuid
    ) {
        $queryBuilder = $this->createQueryBuilder()
            ->delete($this->getTableName())
            ->where(SchemaConfigurator::UUID_COLUMN . ' = ?')
            ->andWhere(SchemaConfigurator::RELATION_ID_COLUMN . ' = ?')
            ->setParameters([$uuid->toNative(), $offerUuid->toNative()]);

        $queryBuilder->execute();
    }
}
