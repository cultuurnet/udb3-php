<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Model\ValueObject\Calendar\DateRange;
use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * Provices a class for a timestamp.
 * @todo Replace by CultuurNet\UDB3\Model\ValueObject\Calendar\DateRange.
 */
class Timestamp implements SerializableInterface
{

    /**
     * @var DateTimeInterface
     */
    protected $startDate;

    /**
     * @var DateTimeInterface
     */
    protected $endDate;

    /**
     * Constructor
     *
     * @param DateTimeInterface $startDate
     * @param DateTimeInterface $endDate
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        DateTimeInterface $startDate,
        DateTimeInterface $endDate
    ) {
        if ($endDate < $startDate) {
            throw new InvalidArgumentException('End date can not be earlier than start date.');
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return DateTimeInterface
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @return DateTimeInterface
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        return new static(
            DateTime::createFromFormat(DateTime::ATOM, $data['startDate']),
            DateTime::createFromFormat(DateTime::ATOM, $data['endDate'])
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return [
            'startDate' => $this->startDate->format(DateTime::ATOM),
            'endDate' => $this->endDate->format(DateTime::ATOM),
        ];
    }

    /**
     * @param DateRange $dateRange
     * @return self
     */
    public static function fromUdb3ModelDateRange(DateRange $dateRange)
    {
        return new Timestamp(
            $dateRange->getFrom(),
            $dateRange->getTo()
        );
    }
}
