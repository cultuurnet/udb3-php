<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\HTML\Uitpas;

interface UitpasEventInfoServiceInterface
{
    /**
     * @param string $eventId
     * @return UitpasEventInfo
     */
    public function getEventInfo($eventId);
}
