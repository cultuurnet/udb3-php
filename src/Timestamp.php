<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Model\ValueObject\Calendar\DateRange;
use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

final class Timestamp implements SerializableInterface
{
    /**
     * @var DateTimeInterface
     */
    protected $startDate;

    /**
     * @var DateTimeInterface
     */
    protected $endDate;

    final public function __construct(
        DateTimeInterface $startDate,
        DateTimeInterface $endDate
    ) {
        if ($endDate < $startDate) {
            throw new InvalidArgumentException('End date can not be earlier than start date.');
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getStartDate(): DateTimeInterface
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeInterface
    {
        return $this->endDate;
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
        return [
            'startDate' => $this->startDate->format(DateTime::ATOM),
            'endDate' => $this->endDate->format(DateTime::ATOM),
        ];
    }

    public static function fromUdb3ModelDateRange(DateRange $dateRange): Timestamp
    {
        return new Timestamp(
            $dateRange->getFrom(),
            $dateRange->getTo()
        );
    }

    public function equals(Timestamp $otherTimestamp): bool
    {
        return $this->serialize() === $otherTimestamp->serialize();
    }
}
