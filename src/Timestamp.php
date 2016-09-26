<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use DateTime;
use DateTimeInterface;
use ValueObjects\DateTime\Date;

/**
 * Provices a class for a timestamp.
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
     */
    public function __construct(
        DateTimeInterface $startDate,
        DateTimeInterface $endDate
    ) {
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
}
