<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;

/**
 * Calendar for events and places.
 */
class Calendar implements CalendarInterface, JsonLdSerializableInterface, SerializableInterface
{

    /**
     * @var string
     */
    protected $type = null;

    /**
     * @var string
     */
    protected $startDate = null;

    /**
     * @var string
     */
    protected $endDate = null;

    /**
     * @var \CultuurNet\UDB3\Timestamp[]
     */
    protected $timestamps = array();

    /**
     * @var Array
     */
    protected $openingHours = array();

    const SINGLE = "single";
    const MULTIPLE = "multiple";
    const PERIODIC = "periodic";
    const PERMANENT = "permanent";

    /**
     * @param string $calendarType
     * @param string $startDate
     * @param string $endDate
     * @param array $timestamps
     * @param array $openingHours
     */
    public function __construct(
        $calendarType,
        $startDate = '',
        $endDate = '',
        $timestamps = array(),
        $openingHours = array()
    ) {
        if ($calendarType != self::PERMANENT &&
            $calendarType != self::MULTIPLE &&
            $calendarType != self::PERIODIC &&
            $calendarType != self::SINGLE) {
            throw new \UnexpectedValueException('Invalid calendar type: ' . $calendarType . '==' . self::PERMANENT . ' given.');
        }

        if (($calendarType == self::MULTIPLE || $calendarType == self::SINGLE) && empty($startDate)) {
            throw new \UnexpectedValueException('Start date can not be empty for calendar type: ' . $calendarType . '.');
        }

        $this->type = $calendarType;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->timestamps = $timestamps;
        $this->openingHours = $openingHours;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $serializedTimestamps = array_map(
            function (Timestamp $timestamp) {
                return $timestamp->serialize();
            },
            $this->timestamps
        );

        return [
          'type' => $this->getType(),
          'startDate' => $this->startDate,
          'endDate' => $this->endDate,
          'timestamps' => $serializedTimestamps,
          'openingHours' => $this->openingHours,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        foreach ($data['timestamps'] as $key => $timestamp) {
            $data['timestamps'][$key] = Timestamp::deserialize($timestamp);
        }

        return new static(
            $data['type'],
            $data['startDate'],
            $data['endDate'],
            $data['timestamps'],
            $data['openingHours']
        );
    }

    /**
     * @inheritdoc
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Get the end date
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @inheritdoc
     */
    public function getOpeningHours()
    {
        return $this->openingHours;
    }

    /**
     * @inheritdoc
     */
    public function getTimestamps()
    {
        return $this->timestamps;
    }

    /**
     * Return the jsonLD version of a calendar.
     */
    public function toJsonLd()
    {
        $jsonLd = [];

        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        $jsonLd['calendarType'] = $this->getType();
        // All calendar types allow startDate (and endDate).
        // One timestamp - full day.
        // One timestamp - start hour.
        // One timestamp - start and end hour.
        if (!empty($startDate)) {
            $jsonLd['startDate'] = $startDate;
        }

        if (!empty($endDate)) {
            $jsonLd['endDate'] = $endDate;
        }

        $timestamps = $this->getTimestamps();
        if (!empty($timestamps)) {
            $jsonLd['subEvent'] = array();
            foreach ($timestamps as $timestamp) {
                $jsonLd['subEvent'][] = array(
                  '@type' => 'Event',
                  'startDate' => $timestamp->getStartDate(),
                  'endDate' => $timestamp->getEndDate(),
                );
            }
        }

        // Period.
        // Period with openingtimes.
        // Permanent - "altijd open".
        // Permanent - with openingtimes
        $openingHours = $this->getOpeningHours();
        if (!empty($openingHours)) {
            $jsonLd['openingHours'] = array();
            foreach ($openingHours as $openingHour) {
                $schedule = array('dayOfWeek' => $openingHour->dayOfWeek);
                if (!empty($openingHour->opens)) {
                    $schedule['opens'] = $openingHour->opens;
                }
                if (!empty($openingHour->closes)) {
                    $schedule['closes'] = $openingHour->closes;
                }
                $jsonLd['openingHours'][] = $schedule;
            }
        }

        return $jsonLd;
    }
}
