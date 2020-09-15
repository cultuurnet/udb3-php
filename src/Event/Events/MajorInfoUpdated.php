<?php

namespace CultuurNet\UDB3\Event\Events;

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
final class MajorInfoUpdated extends AbstractEvent
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
        string $eventId,
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
    public function getTitle(): Title
    {
        return $this->title;
    }

    /**
     * @return EventType
     */
    public function getEventType(): EventType
    {
        return $this->eventType;
    }

    /**
     * @return Theme|null
     */
    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    /**
     * @return CalendarInterface
     */
    public function getCalendar(): CalendarInterface
    {
        return $this->calendar;
    }

    /**
     * @return LocationId
     */
    public function getLocation(): LocationId
    {
        return $this->location;
    }


    /**
     * @return array
     */
    public function serialize(): array
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
    public static function deserialize(array $data): MajorInfoUpdated
    {
        return new self(
            $data['item_id'],
            new Title($data['title']),
            EventType::deserialize($data['event_type']),
            new LocationId($data['location']),
            Calendar::deserialize($data['calendar']),
            empty($data['theme']) ? null : Theme::deserialize($data['theme'])
        );
    }
}
