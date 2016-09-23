<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Timestamp;
use DateTime;
use DateTimeInterface;

/**
 * Calendar for events and places.
 */
class Calendar implements CalendarInterface, JsonLdSerializableInterface, SerializableInterface
{

    /**
     * @var CalendarType
     */
    protected $type = null;

    /**
     * @var DateTimeInterface
     */
    protected $startDate = null;

    /**
     * @var DateTimeInterface
     */
    protected $endDate = null;

    /**
     * @var Timestamp[]
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
     * @param CalendarType $type
     * @param DateTimeInterface|null $startDate
     * @param DateTimeInterface|null $endDate
     * @param Timestamp[] $timestamps
     * @param array $openingHours
     */
    public function __construct(
        CalendarType $type,
        DateTimeInterface $startDate = null,
        DateTimeInterface $endDate = null,
        array $timestamps = array(),
        array $openingHours = array()
    ) {
        if (($type->is(CalendarType::MULTIPLE()) || $type->is(CalendarType::SINGLE())) && isNull($startDate)) {
            throw new \UnexpectedValueException('Start date can not be empty for calendar type: ' . $type . '.');
        }

        $this->type = $type;
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

        $calendar = [
          'type' => $this->getType()->toNative(),
        ];

        isNull($this->startDate) ?: $calendar['startDate'] = $this->startDate;
        isNull($this->endDate) ?: $calendar['endDate'] = $this->endDate;
        isEmpty($serializedTimestamps) ?: $calendar['timestamps'] = $serializedTimestamps;
        isEmpty($this->openingHours) ?: $calendar['openingHours'] = $this->openingHours;

        return $calendar;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            CalendarType::fromNative($data['type']),
            isset($data['startDate']) ? DateTime::createFromFormat('c', $data['startDate']) : null,
            isset($data['endDate']) ? DateTime::createFromFormat('c', $data['endDate']) : null,
            isset($data['timestamps']) ? array_map(
                function (Timestamp $timestamp) {
                    return $timestamp->serialize();
                },
                $data['timestamps']
            ) : [],
            isset($data['openingHours']) ? $data['openingHours'] : []
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
     * @inheritdoc
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

        $jsonLd['calendarType'] = $this->getType()->toNative();
        // All calendar types allow startDate (and endDate).
        // One timestamp - full day.
        // One timestamp - start hour.
        // One timestamp - start and end hour.
        isNull($this->startDate) ?: $jsonLd['startDate'] = $this->startDate;
        isNull($this->endDate) ?: $jsonLd['endDate'] = $this->startDate;


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
