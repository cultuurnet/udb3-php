<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\Doctrine\AbstractDBALRepository;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\OfferLabelRelation;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\ReadRepositoryInterface;
use ValueObjects\Identity\UUID;

class ReadRepository extends AbstractDBALRepository implements ReadRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function getOfferLabelRelations(UUID $labelId)
    {
        $aliases = $this->getAliases();
        $whereUuid = SchemaConfigurator::UUID_COLUMN . ' = ?';

        $queryBuilder = $this->createQueryBuilder()->select($aliases)
            ->from($this->getTableName()->toNative())
            ->where($whereUuid)
            ->setParameters([$labelId]);

        $statement = $queryBuilder->execute();

        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $offerLabelRelation = OfferLabelRelation::fromRelationalData($row);
            yield $offerLabelRelation;
        }
    }

    /**
     * @return array
     */
    private function getAliases()
    {
        return [
            SchemaConfigurator::UUID_COLUMN,
            SchemaConfigurator::OFFER_TYPE_COLUMN,
            SchemaConfigurator::OFFER_ID_COLUMN
        ];
    }
}
