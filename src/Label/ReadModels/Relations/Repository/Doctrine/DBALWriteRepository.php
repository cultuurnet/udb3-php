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
        OfferType $relationType,
        StringLiteral $relationId
    ) {
        $queryBuilder = $this->createQueryBuilder()
            ->insert($this->getTableName())
            ->values([
                SchemaConfigurator::UUID_COLUMN => '?',
                SchemaConfigurator::OFFER_TYPE_COLUMN => '?',
                SchemaConfigurator::OFFER_ID_COLUMN => '?'
            ])
            ->setParameters([
                $uuid->toNative(),
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
            ->andWhere(SchemaConfigurator::OFFER_ID_COLUMN . ' = ?')
            ->setParameters([$uuid->toNative(), $offerUuid->toNative()]);

        $queryBuilder->execute();
    }
}
