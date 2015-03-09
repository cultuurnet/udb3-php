<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

use CultuurNet\UDB3\Event\Event;

/**
 * Imports cultural events from UDB2 into UDB3.
 */
interface EventImporterInterface
{
    /**
     * @param string $eventId
     * @return Event
     */
    public function updateEventFromUDB2($eventId);

    /**
     * @param string $eventId
     * @return Event
     */
    public function createEventFromUDB2($eventId);
}
