<?php

namespace CultuurNet\UDB3\Event\Editing;

use CultuurNet\UDB3\Event\Event;

interface EventVariationRepositoryInterface
{
    /**
     * @param string $originalEventId
     * @return Event
     * @throws EventVariationNotFoundException
     */
    public function getPersonalVariation($originalEventId);
}
