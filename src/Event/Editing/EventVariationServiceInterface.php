<?php

namespace CultuurNet\UDB3\Event\Editing;

use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3\UDB2\EventNotFoundException;

interface EventVariationServiceInterface
{
    /**
     * Returns the personal variation of an existing event
     *
     * @param string $originalEventId
     * @param string $ownerId
     *
     * @return Event
     * @throws EventNotFoundException
     */
    public function getPersonalEventVariation($originalEventId, $ownerId);
}
