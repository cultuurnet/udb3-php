<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\CalendarInterface;

class EventCreated extends EventEvent
{
 
    use \CultuurNet\UDB3\EventCreatedTrait;
    
    /**
     * @param string $eventId
     * @param Title $title
     * @param string $location
     * @param \DateTime $date
     */
    public function __construct($eventId, Title $title, EventType $eventType, Location $location, CalendarInterface $calendar, $theme = null)
    {
        parent::__construct($eventId);

        $this->setTitle($title);
        $this->setEventType($eventType);
        $this->setLocation($location);
        $this->setCalendar($calendar);
        
        if(!isset($theme)) {
          $this->setTheme($theme);
        }
    }
    
}
