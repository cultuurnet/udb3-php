<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Event\ValueObjects\Status;
use CultuurNet\UDB3\Event\ValueObjects\StatusType;
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
     * @var Status
     */
    private $status;

    final public function __construct(
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
        Status $status = null
    ) {
        if ($endDate < $startDate) {
            throw new InvalidArgumentException('End date can not be earlier than start date.');
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status ?? new Status(StatusType::available(), []);
    }

    public function getStartDate(): DateTimeInterface
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeInterface
    {
        return $this->endDate;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public static function deserialize(array $data): Timestamp
    {
        $status = null;
        if (isset($data['status']) && isset($data['statusReason'])) {
            $status = Status::deserialize($data);
        }

        return new static(
            DateTime::createFromFormat(DateTime::ATOM, $data['startDate']),
            DateTime::createFromFormat(DateTime::ATOM, $data['endDate']),
            $status
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
            $this->status->serialize()
        );
    }

    public function toJsonLd(): array
    {
        $jsonLd = $this->serialize();
        $jsonLd['@type'] = 'Event';

        return $jsonLd;
    }

    public static function fromUdb3ModelDateRange(DateRange $dateRange): Timestamp
    {
        $status = null;
        $udb3ModelEventStatus = $dateRange->getEventStatus();
        if (!is_null($udb3ModelEventStatus)) {
            $status = Status::fromUdb3ModelStatus($udb3ModelEventStatus);
        }

        return new Timestamp(
            $dateRange->getFrom(),
            $dateRange->getTo(),
            $status
        );
    }
}
