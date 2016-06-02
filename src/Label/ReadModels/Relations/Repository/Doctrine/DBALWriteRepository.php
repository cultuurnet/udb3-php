<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\Doctrine\AbstractDBALRepository;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\WriteRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class DBALWriteRepository extends AbstractDBALRepository implements WriteRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function save(
        UUID $uuid,
        RelationType $relationType,
        StringLiteral $relationId
    ) {
        $this->getQueryBuilder()->insert($this->getTableName())
            ->values([
                SchemaConfigurator::UUID_COLUMN => '?',
                SchemaConfigurator::RELATION_TYPE_COLUMN => '?',
                SchemaConfigurator::RELATION_ID_COLUMN => '?'
            ])
            ->setParameters([
                $uuid->toNative(),
                $relationType->toNative(),
                $relationId->toNative()
            ]);

        $this->getQueryBuilder()->execute();
    }

    /**
     * @inheritdoc
     */
    public function deleteByUuid(UUID $uuid)
    {
        $this->getQueryBuilder()->delete($this->getTableName())
            ->where(SchemaConfigurator::UUID_COLUMN . ' = ?')
            ->setParameters([$uuid->toNative()]);

        $this->getQueryBuilder()->execute();
    }
}
