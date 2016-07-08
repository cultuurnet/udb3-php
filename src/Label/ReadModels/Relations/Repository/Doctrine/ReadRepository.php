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
