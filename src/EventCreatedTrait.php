<?php

/**
 * @file
 * Contains CultuurNet\UDB3\EventCreatedTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Trait for the DescriptionUpdated events.
 */
trait EventCreatedTrait {
  
    /**
     * @var Title
     */
    private $title;

    /**
     * @var Type
     */
    private $type = NULL;

    /**
     * @var Theme
     */
    private $theme = NULL;

    /**
     * @var Location
     */
    private $location;

    /**
     * @var \CultuurNet\UDB3\CalendarInterface
     */
    private $calendar;


    /**
     * @param Title $title
     */
    private function setTitle(Title $title)
    {
        $this->title = $title;
    }

    /**
     * @param EventType $eventType
     */
    private function setEventType(EventType $eventType)
    {
        $this->type = $eventType;
    }

    /**
     * @param Theme $theme
     */
    private function setTheme(Theme $theme) {
        $this->theme = $theme;
    }

    /**
     * @param CalendarBase $calendar
     */
    private function setCalendar(CalendarInterface $calendar)
    {
        $this->calendar = $calendar;
    }

    /**
     * @param Location $location
     */
    private function setLocation(Location $location)
    {
        $this->location = $location;
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
        return $this->type;
    }

    /**
     * @return Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @return CalendarBase
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * @return Location
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
        $theme = NULL;
        if ($this->getTheme() !== NULL) {
          $theme = $this->getTheme()->serialize();
        }
        return parent::serialize() + array(
            'title' => (string)$this->getTitle(),
            'event_type' => $this->getEventType()->serialize(),
            'theme' => $theme,
            'location' => $this->getLocation()->serialize(),
            'calendar' => $this->getCalendar()->serialize(),
            'type' => array(
                'id' => $this->type->getId(),
                'label' => $this->type->getLabel()
            ),
        );
    }

    /**
     * @return static
     */
    public static function deserialize(array $data)
    {
        $theme = NULL;
        if (!empty($data['theme'])) {
          $theme = Theme::deserialize($data['theme']);
        }
        return new static(
            $data['event_id'],
            new Title($data['title']),
            EventType::deserialize($data['event_type']),
            $theme,
            Location::deserialize($data['location']),
            TimeStamps::deserialize($data['calendar']),
            $data['location'],
            \DateTime::createFromFormat('c', $data['date']),
            new EventType(
                $data['type']['id'],
                $data['type']['label']
            )
        );
    }

}
