<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place;

use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\Title;
use CultuurNet\UDB3\Location;

interface PlaceEditingServiceInterface
{

    /**
     * @param Title $title
     * @param EventType $eventType
     * @param Location $location
     * @param CalendarBase $calendar
     * @param Theme/null $theme
     *
     * @return string $eventId
     */
    public function createPlace(Title $title, EventType $eventType, Location $location, CalendarInterface $calendar, $theme = NULL);
}
