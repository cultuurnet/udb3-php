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
}
