<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Events\EventDeleted.
 */

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\EventEvent;

/**
 * Provides an EventDeleted event.
 */
class EventDeleted extends EventEvent
{

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        parent::__construct($id);
    }
}
