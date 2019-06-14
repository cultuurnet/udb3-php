<?php

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ValueObjects\LocationId;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;

/**
 * Provides a majorInfoUpdated event.
 */
class MajorInfoUpdated extends AbstractEvent implements SerializableInterface
{
    use BackwardsCompatibleEventTrait;

    /**
     * @var Title
     */
    private $title;

    /**
     * @var EventType
     */
    private $eventType;

    /**
     * @var Theme|null
     */
    private $theme;

    /**
     * @var LocationId
     */
    private $location;

    /**
     * @var CalendarInterface
     */
    private $calendar;

    /**
     * @param string $eventId
     * @param Title $title
     * @param EventType $eventType
     * @param LocationId $location
     * @param CalendarInterface $calendar
     * @param Theme|null $theme
     */
    public function __construct(
        $eventId,
        Title $title,
        EventType $eventType,
        LocationId $location,
        CalendarInterface $calendar,
        Theme $theme = null
    ) {
        parent::__construct($eventId);

        $this->title = $title;
        $this->eventType = $eventType;
        $this->location = $location;
        $this->calendar = $calendar;
        $this->theme = $theme;
    }

    /**
     * @return Title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return EventType
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @return Theme|null
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @return CalendarInterface
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * @return LocationId
     */
    public function getLocation()
    {
        return $this->location;
    }


    /**
     * @return array
     */
    public function serialize()
    {
        $theme = null;
        if ($this->getTheme() !== null) {
            $theme = $this->getTheme()->serialize();
        }
        return parent::serialize() + array(
            'title' => (string)$this->getTitle(),
            'event_type' => $this->getEventType()->serialize(),
            'theme' => $theme,
            'location' => $this->getLocation()->toNative(),
            'calendar' => $this->getCalendar()->serialize(),
        );
    }

    /**
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['item_id'],
            new Title($data['title']),
            EventType::deserialize($data['event_type']),
            new LocationId($data['location']),
            Calendar::deserialize($data['calendar']),
            empty($data['theme']) ? null : Theme::deserialize($data['theme'])
        );
    }
}
