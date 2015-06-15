<?php

namespace CultuurNet\UDB3\Variations\ReadModel\Relations;

use CultuurNet\UDB3\Variations\EventVariationNotFoundException;
use CultuurNet\UDB3\Variations\Model\EventVariation;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;

interface RepositoryInterface
{
    /**
     * @param Url $eventUrl
     * @param OwnerId $ownerId
     * @param Purpose $purpose
     * @return EventVariation
     * @throws EventVariationNotFoundException
     */
    public function getOwnerEventVariationByPurpose(
        Url $eventUrl,
        OwnerId $ownerId,
        Purpose $purpose
    );

    /**
     * @param Id $variationId
     * @param Url $eventUrl
     * @param OwnerId $ownerId
     * @param Purpose $purpose
     * @return mixed
     */
    public function storeRelations(
        Id $variationId,
        Url $eventUrl,
        OwnerId $ownerId,
        Purpose $purpose
    );
}
