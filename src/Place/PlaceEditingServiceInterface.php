<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Location;
use Drupal\Core\Updater\Theme;

interface PlaceEditingServiceInterface
{

    /**
     * @param Title $title
     * @param EventType $eventType
     * @param Theme $theme
     * @param Location $location
     * @param CalendarBase $calendar
     *
     * @return string $eventId
     */
    public function createPlace(Title $title, EventType $eventType, Theme $theme, Location $location, CalendarInterface $calendar);
}
