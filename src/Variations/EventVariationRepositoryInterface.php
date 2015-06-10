<?php

namespace CultuurNet\UDB3\Variations;

use CultuurNet\UDB3\Event\Event;

interface EventVariationRepositoryInterface
{
    /**
     * @param string $originalEventId
     * @return Event
     * @throws EventVariationNotFoundException
     */
    public function getPersonalVariation($originalEventId);

    /**
     * @param $originalEventId
     * @param $eventVariationId
     * @param $ownerId
     * @return mixed
     */
    public function storePersonalVariation($originalEventId, $eventVariationId, $ownerId);
}
