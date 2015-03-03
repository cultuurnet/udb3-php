<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place;

use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Title;

interface PlaceEditingServiceInterface
{

    /**
     * @param Title $title
     * @param EventType $eventType
     * @param Address $address
     * @param CalendarBase $calendar
     * @param Theme/null $theme
     *
     * @return string $eventId
     */
    public function createPlace(Title $title, EventType $eventType, Address $address, CalendarInterface $calendar, $theme = null);
}
