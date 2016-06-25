<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository\Doctrine;

use CultuurNet\UDB3\Label\ReadModels\Doctrine\AbstractDBALRepository;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\OfferLabelRelation;
use CultuurNet\UDB3\Label\ReadModels\Relations\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Offer\OfferType;
use ValueObjects\Identity\UUID;

class ReadRepository extends AbstractDBALRepository implements ReadRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function getOfferLabelRelations(UUID $labelId)
    {
//        TODO: This one gives a cryptic error message: " Illegal offset type"
//        $queryBuilder = $this->createQueryBuilder()
//            ->select(SchemaConfigurator::RELATION_ID_COLUMN)
//            ->from($this->getTableName())
//            ->where(SchemaConfigurator::UUID_COLUMN . ' = :label_id')
//            ->setParameter(':label_id', (string) $labelId);
//
//        $results = $queryBuilder->execute();

        $query = $this
            ->getConnection()
            ->prepare(
                'SELECT * ' .
                ' FROM ' . $this->getTableName() .
                ' WHERE ' . SchemaConfigurator::UUID_COLUMN . ' = ?'
            );

        $query->bindValue(1, $labelId, 'string');
        $query->execute();

        return array_map(
            array(OfferLabelRelation::class, 'fromRelationalData'),
            $query->fetchAll(\PDO::FETCH_ASSOC)
        );
    }
}
