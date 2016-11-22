<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;

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
     * @var array
     */
    protected $openingHours = array();

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
        if (($type->is(CalendarType::MULTIPLE()) || $type->is(CalendarType::SINGLE())) && empty($startDate)) {
            throw new \UnexpectedValueException('Start date can not be empty for calendar type: ' . $type . '.');
        }

        if ($type->is(CalendarType::PERIODIC()) && (empty($startDate) || empty($endDate))) {
            throw new \UnexpectedValueException('A period should have a start- and end-date.');
        }

        $this->type = $type->toNative();
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
        return CalendarType::fromNative($this->type);
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
          'type' => $this->type,
        ];

        empty($this->startDate) ?: $calendar['startDate'] = $this->startDate->format(DateTime::ATOM);
        empty($this->endDate) ?: $calendar['endDate'] = $this->endDate->format(DateTime::ATOM);
        empty($serializedTimestamps) ?: $calendar['timestamps'] = $serializedTimestamps;
        empty($this->openingHours) ?: $calendar['openingHours'] = $this->openingHours;

        return $calendar;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            CalendarType::fromNative($data['type']),
            !empty($data['startDate']) ? self::deserializeDateTime($data['startDate']) : null,
            !empty($data['endDate']) ? self::deserializeDateTime($data['endDate']) : null,
            !empty($data['timestamps']) ? array_map(
                function ($timestamp) {
                    return Timestamp::deserialize($timestamp);
                },
                $data['timestamps']
            ) : [],
            !empty($data['openingHours']) ? $data['openingHours'] : []
        );
    }

    /**
     * This deserialization function takes into account old data that might be missing a timezone.
     * It will fall back to creating a DateTime object and assume Brussels.
     * If this still fails an error will be thrown.
     *
     * @param $dateTimeData
     * @return DateTime
     *
     * @throws InvalidArgumentException
     */
    private static function deserializeDateTime($dateTimeData)
    {
        $dateTime = DateTime::createFromFormat(DateTime::ATOM, $dateTimeData);

        if ($dateTime === false) {
            $dateTime = DateTime::createFromFormat('Y-m-d\TH:i:s', $dateTimeData, new DateTimeZone('Europe/Brussels'));

            if (!$dateTime) {
                throw new InvalidArgumentException('Invalid date string provided for timestamp, ISO8601 expected!');
            }
        }

        return $dateTime;
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
        empty($this->startDate) ?: $jsonLd['startDate'] = $this->getStartDate()->format(DateTime::ATOM);
        empty($this->endDate) ?: $jsonLd['endDate'] = $this->getEndDate()->format(DateTime::ATOM);


        $timestamps = $this->getTimestamps();
        if (!empty($timestamps)) {
            $jsonLd['subEvent'] = array();
            foreach ($timestamps as $timestamp) {
                $jsonLd['subEvent'][] = array(
                  '@type' => 'Event',
                  'startDate' => $timestamp->getStartDate()->format(DateTime::ATOM),
                  'endDate' => $timestamp->getEndDate()->format(DateTime::ATOM),
                );
            }
        }

        // Period.
        // Period with openingtimes.
        // Permanent - "altijd open".
        // Permanent - with openingtimes
        $openingHours = $this->getOpeningHours();
        if (!empty($openingHours)) {
            $jsonLd['openingHours'] = (array) $openingHours;
        }

        return $jsonLd;
    }
}
