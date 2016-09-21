<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarInterface;
use CultuurNet\UDB3\Event\EventEvent;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Offer\WorkflowStatus;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use DateTimeImmutable;

/**
 * Event when an event is created.
 */
class EventCreated extends EventEvent
{

    /**
     * @var Title
     */
    private $title;

    /**
     * @var EventType
     */
    private $eventType = null;

    /**
     * @var Theme
     */
    private $theme = null;

    /**
     * @var Location
     */
    private $location;

    /**
     * @var CalendarInterface
     */
    private $calendar;

    /**
     * @var DateTimeImmutable|null
     */
    private $publicationDate = null;

    /**
     * @var WorkflowStatus
     */
    private $workflowStatus = null;

    /**
     * @param string $eventId
     * @param Title $title
     * @param EventType $eventType
     * @param Location $location
     * @param CalendarInterface $calendar
     * @param Theme|null $theme
     * @param DateTimeImmutable|null $publicationDate
     * @param WorkflowStatus|null $workflowStatus
     */
    public function __construct(
        $eventId,
        Title $title,
        EventType $eventType,
        Location $location,
        CalendarInterface $calendar,
        Theme $theme = null,
        DateTimeImmutable $publicationDate = null,
        WorkflowStatus $workflowStatus = null
    ) {
        parent::__construct($eventId);

        $this->title = $title;
        $this->eventType = $eventType;
        $this->location = $location;
        $this->calendar = $calendar;
        $this->theme = $theme;
        $this->publicationDate = $publicationDate;
        $this->workflowStatus = $workflowStatus ? $workflowStatus : WorkflowStatus::READY_FOR_VALIDATION();
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
     * @return Theme
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
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * @return WorkflowStatus
     */
    public function getWorkflowStatus()
    {
        return $this->workflowStatus;
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
        $publicationDate = null;
        if (!is_null($this->getPublicationDate())) {
            $publicationDate = $this->getPublicationDate()->format(\DateTime::ISO8601);
        }
        return parent::serialize() + array(
            'title' => (string)$this->getTitle(),
            'event_type' => $this->getEventType()->serialize(),
            'theme' => $theme,
            'location' => $this->getLocation()->serialize(),
            'calendar' => $this->getCalendar()->serialize(),
            'publication_date' => $publicationDate,
            'workflow_status' => $this->workflowStatus->toNative()
        );
    }

    /**
     * @return static
     */
    public static function deserialize(array $data)
    {
        $theme = null;
        if (!empty($data['theme'])) {
            $theme = Theme::deserialize($data['theme']);
        }
        $publicationDate = null;
        if (!empty($data['publication_date'])) {
            $publicationDate = DateTimeImmutable::createFromFormat(
                \DateTime::ISO8601,
                $data['publication_date']
            );
        }

        $workflowStatus = !empty($data['workflow_status']) ?
            WorkflowStatus::fromNative($data['workflow_status']) : WorkflowStatus::READY_FOR_VALIDATION();

        return new static(
            $data['event_id'],
            new Title($data['title']),
            EventType::deserialize($data['event_type']),
            Location::deserialize($data['location']),
            Calendar::deserialize($data['calendar']),
            $theme,
            $publicationDate,
            $workflowStatus
        );
    }
}
