<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;

/**
 * Provices a class for a timestamp.
 */
class Timestamp implements SerializableInterface
{

    /**
     * @var string
     */
    protected $startDate;

    /**
     * @var string
     */
    protected $endDate;

    /**
     * Constructor
     *
     * @param string $startDate
     * @param string $endDate
     */
    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

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
            $data['startDate'], $data['endDate']
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];
    }
}
