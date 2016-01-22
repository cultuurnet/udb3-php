<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use ValueObjects\String\String;

interface SecurityInterface
{
    /**
     * @param String $eventId
     * @return boolean
     */
    public function allowsUpdateWithCdbXml(String $eventId);

    /**
     * Returns if the event allows updates through the UDB3 core APIs.
     *
     * @param String $eventId
     * @return boolean
     */
    public function allowsUpdates(String $eventId);
}
