<?php

namespace CultuurNet\UDB3\Variations;

use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;

interface EventVariationServiceInterface
{
    public function createEventVariation(
        Url $eventUrl,
        OwnerId $ownerId,
        Purpose $purpose,
        Description $description
    );
}
