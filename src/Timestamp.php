<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Event\ValueObjects\EventStatus;
use CultuurNet\UDB3\Event\ValueObjects\EventStatusType;
use CultuurNet\UDB3\Model\ValueObject\Calendar\DateRange;
use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

final class Timestamp implements SerializableInterface
{
    /**
     * @var DateTimeInterface
     */
    private $startDate;

    /**
     * @var DateTimeInterface
     */
    private $endDate;

    /**
     * @var EventStatus|null
     */
    private $eventStatus;

    final public function __construct(
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
        EventStatus $eventStatus = null
    ) {
        if ($endDate < $startDate) {
            throw new InvalidArgumentException('End date can not be earlier than start date.');
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->eventStatus = new EventStatus(EventStatusType::scheduled(), []);
        if ($eventStatus) {
            $this->eventStatus = $eventStatus;
        }
    }

    public function getStartDate(): DateTimeInterface
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeInterface
    {
        return $this->endDate;
    }

    public function getEventStatus(): EventStatus
    {
        return $this->eventStatus;
    }

    public static function deserialize(array $data): Timestamp
    {
        return new static(
            DateTime::createFromFormat(DateTime::ATOM, $data['startDate']),
            DateTime::createFromFormat(DateTime::ATOM, $data['endDate'])
        );
    }

    public function serialize(): array
    {
        $serialized = [
            'startDate' => $this->startDate->format(DateTime::ATOM),
            'endDate' => $this->endDate->format(DateTime::ATOM),
        ];

        return \array_merge(
            $serialized,
            $this->eventStatus->serialize()
        );
    }

    public static function fromUdb3ModelDateRange(DateRange $dateRange): Timestamp
    {
        return new Timestamp(
            $dateRange->getFrom(),
            $dateRange->getTo()
        );
    }
}
