<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use DateTime;
use DateTimeInterface;

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
    public function __construct($startDate, $endDate)
    {
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
            DateTime::createFromFormat('c', $data['startDate']),
            DateTime::createFromFormat('c', $data['endDate'])
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return [
            'startDate' => $this->startDate->format('c'),
            'endDate' => $this->endDate->format('c'),
        ];
    }
}
