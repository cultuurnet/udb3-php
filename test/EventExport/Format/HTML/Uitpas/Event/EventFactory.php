<?php

namespace CultuurNet\UDB3\EventExport\Format\HTML\Uitpas\Event;

use \CultureFeed_Uitpas_Event_CultureEvent as Event;

class EventFactory
{
    /**
     * @param float|int $points
     */
    public function buildEventWithPoints($points)
    {
        $event = new Event();
        $event->numberOfPoints = $points;
        return $event;
    }
}
