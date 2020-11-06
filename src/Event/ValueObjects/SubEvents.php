<?php

declare(strict_types=1);

namespace CultuurNet\UDB3\Event\ValueObjects;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Timestamp;

final class SubEvents
{
    /**
     * @var SubEvent[]
     */
    private $subEvents;

    private function __construct(SubEvent ...$subEvents)
    {
        $this->subEvents = $subEvents;
    }

    public static function createEmpty(): SubEvents
    {
        return new SubEvents(...[]);
    }

    public static function createFromCalendar(Calendar $calendar): SubEvents
    {
        if (empty($calendar->getTimestamps())) {
            return self::createEmpty();
        }

        $subEvents = [];
        // TODO III-3570: Take into account a calendar with the same timestamps.
        foreach ($calendar->getTimestamps() as $timestamp) {
            $subEvents[] = new SubEvent($timestamp, Status::scheduled());
        }

        return new SubEvents(...$subEvents);
    }

    public function getSubEvents(): array
    {
        return $this->subEvents;
    }

    public function hasSubEvent(SubEvent $subEventToSearch): bool
    {
        foreach ($this->subEvents as $subEvent) {
            if ($subEvent->equals($subEventToSearch)) {
                return true;
            }
        }

        return false;
    }

    public function hasSubEventWithTimestamp(Timestamp $timestampToSearch): bool
    {
        foreach ($this->subEvents as $subEvent) {
            if ($subEvent->getTimestamp()->equals($timestampToSearch)) {
                return true;
            }
        }

        return false;
    }

    public function addSubEvent(SubEvent $subEvent): SubEvents
    {
        if ($this->hasSubEventWithTimestamp($subEvent->getTimestamp())) {
            return $this;
        }

        $this->subEvents[] = $subEvent;

        return $this;
    }

    public function updateSubEvent(SubEvent $subEvent): SubEvents
    {
        if (!$this->hasSubEventWithTimestamp($subEvent->getTimestamp())) {
            return $this;
        }

        $this->removeSubEventWithTimestamp($subEvent->getTimestamp());
        $this->subEvents[] = $subEvent;

        return $this;
    }

    private function removeSubEventWithTimestamp(Timestamp $timestamp): SubEvents
    {
        $nrOfSubEvents = count($this->subEvents);
        for ($index = 0; $index < $nrOfSubEvents; $index++) {
            if ($this->subEvents[$index]->getTimestamp()->equals($timestamp)) {
                unset($this->subEvents[$index]);
            }
        }

        $this->subEvents = \array_values($this->subEvents);

        return $this;
    }
}
