<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Events\PlaceDeleted.
 */

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Place\PlaceEvent;

/**
 * Provides an PlaceDeleted event.
 */
class PlaceDeleted extends PlaceEvent
{

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        parent::__construct($id);
    }
}
