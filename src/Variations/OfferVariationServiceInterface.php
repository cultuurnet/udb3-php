<?php

namespace CultuurNet\UDB3\Variations;

use Broadway\Repository\AggregateNotFoundException;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Variations\Model\OfferVariation;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;

interface OfferVariationServiceInterface
{
    /**
     * @param IriOfferIdentifier $identifier
     * @param OwnerId $ownerId
     * @param Purpose $purpose
     * @param Description $description
     *
     * @return OfferVariation
     */
    public function createEventVariation(
        IriOfferIdentifier $identifier,
        OwnerId $ownerId,
        Purpose $purpose,
        Description $description
    );

    /**
     * @param Id $id
     * @param Description $description
     *
     * @throws AggregateNotFoundException
     * @throws AggregateDeletedException
     */
    public function editDescription(Id $id, Description $description);

    /**
     * Delete a variation
     *
     * @param Id $id
     *
     * @throws AggregateNotFoundException
     * @throws AggregateDeletedException
     */
    public function deleteEventVariation(Id $id);
}
