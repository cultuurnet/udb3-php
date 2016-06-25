<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository;

use ValueObjects\Identity\UUID;

interface ReadRepositoryInterface
{
    /**
     * @param UUID $labelId
     * @return OfferLabelRelation[]
     */
    public function getOfferLabelRelations(UUID $labelId);
}
