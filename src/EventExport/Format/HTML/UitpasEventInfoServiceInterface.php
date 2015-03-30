<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\HTML;

interface UitpasEventInfoServiceInterface
{
    /**
     * @param string $eventId
     * @return UitpasEventInfo
     */
    public function getEventInfo($eventId);
}
