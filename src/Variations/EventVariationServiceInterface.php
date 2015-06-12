<?php

namespace CultuurNet\UDB3\Variations;

use CultuurNet\UDB3\Variations\Model\EventVariation;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;

interface EventVariationServiceInterface
{
    /**
     * @param Url $eventUrl
     * @param OwnerId $ownerId
     * @param Purpose $purpose
     * @param Description $description
     *
     * @return EventVariation
     */
    public function createEventVariation(
        Url $eventUrl,
        OwnerId $ownerId,
        Purpose $purpose,
        Description $description
    );

    /**
     * @param Id $id
     * @param Description $description
     */
    public function editDescription(Id $id, Description $description);
}
