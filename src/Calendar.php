<?php

namespace CultuurNet\UDB3;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Calendar\OpeningHour;
use CultuurNet\UDB3\Model\ValueObject\Calendar\Calendar as Udb3ModelCalendar;
use CultuurNet\UDB3\Model\ValueObject\Calendar\CalendarWithDateRange;
use CultuurNet\UDB3\Model\ValueObject\Calendar\CalendarWithOpeningHours;
use CultuurNet\UDB3\Model\ValueObject\Calendar\CalendarWithSubEvents;
use CultuurNet\UDB3\Model\ValueObject\Calendar\DateRange;
use CultuurNet\UDB3\Model\ValueObject\Calendar\OpeningHours\OpeningHour as Udb3ModelOpeningHour;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;

final class Calendar implements CalendarInterface, JsonLdSerializableInterface, SerializableInterface
{
    /**
     * @var CalendarType
     */
    protected $type;

    /**
     * @var DateTimeInterface
     */
    protected $startDate;

    /**
     * @var DateTimeInterface
     */
    protected $endDate;

    /**
     * @var Timestamp[]
     */
    protected $timestamps = array();

    /**
     * @var OpeningHour[]
     */
    protected $openingHours = array();

    /**
     * @param CalendarType $type
     * @param DateTimeInterface|null $startDate
     * @param DateTimeInterface|null $endDate
     * @param Timestamp[] $timestamps
     * @param OpeningHour[] $openingHours
     */
    public function __construct(
        CalendarType $type,
        ?DateTimeInterface $startDate = null,
        ?DateTimeInterface $endDate = null,
        array $timestamps = [],
        array $openingHours = []
    ) {
        if (empty($timestamps) && ($type->is(CalendarType::SINGLE()) || $type->is(CalendarType::MULTIPLE()))) {
            throw new \UnexpectedValueException('A single or multiple calendar should have timestamps.');
        }

        if (($startDate === null || $endDate === null) && $type->is(CalendarType::PERIODIC())) {
            throw new \UnexpectedValueException('A period should have a start- and end-date.');
        }

        foreach ($timestamps as $timestamp) {
            if (!is_a($timestamp, Timestamp::class)) {
                throw new \InvalidArgumentException('Timestamps should have type TimeStamp.');
            }
        }

        foreach ($openingHours as $openingHour) {
            if (!is_a($openingHour, OpeningHour::class)) {
                throw new \InvalidArgumentException('OpeningHours should have type OpeningHour.');
            }
        }

        $this->type = $type->toNative();
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->openingHours = $openingHours;

        usort($timestamps, function (Timestamp $timestamp, Timestamp $otherTimestamp) {
            return $timestamp->getStartDate() <=> $otherTimestamp->getStartDate();
        });

        $this->timestamps = $timestamps;

    }

    public function getType(): CalendarType
    {
        return CalendarType::fromNative($this->type);
    }

    public function serialize(): array
    {
        $serializedTimestamps = array_map(
            function (Timestamp $timestamp) {
                return $timestamp->serialize();
            },
            $this->timestamps
        );

        $serializedOpeningHours = array_map(
            function (OpeningHour $openingHour) {
                return $openingHour->serialize();
            },
            $this->openingHours
        );

        $calendar = [
            'type' => $this->type,
        ];

        empty($this->startDate) ?: $calendar['startDate'] = $this->startDate->format(DateTime::ATOM);
        empty($this->endDate) ?: $calendar['endDate'] = $this->endDate->format(DateTime::ATOM);
        empty($serializedTimestamps) ?: $calendar['timestamps'] = $serializedTimestamps;
        empty($serializedOpeningHours) ?: $calendar['openingHours'] = $serializedOpeningHours;

        return $calendar;
    }

    public static function deserialize(array $data): Calendar
    {
        $calendarType = CalendarType::fromNative($data['type']);

        // Backwards compatibility for serialized single or multiple calendar types that are missing timestamps but do
        // have a start and end date.
        $defaultTimeStamps = [];
        if ($calendarType->sameValueAs(CalendarType::SINGLE()) || $calendarType->sameValueAs(CalendarType::MULTIPLE())) {
            $defaultTimeStampStartDate = !empty($data['startDate']) ? self::deserializeDateTime($data['startDate']) : null;
            $defaultTimeStampEndDate = !empty($data['endDate']) ? self::deserializeDateTime($data['endDate']) : $defaultTimeStampStartDate;
            $defaultTimeStamp = $defaultTimeStampStartDate && $defaultTimeStampEndDate ? new Timestamp($defaultTimeStampStartDate, $defaultTimeStampEndDate) : null;
            $defaultTimeStamps = $defaultTimeStamp ? [$defaultTimeStamp] : [];
        }

        return new self(
            $calendarType,
            !empty($data['startDate']) ? self::deserializeDateTime($data['startDate']) : null,
            !empty($data['endDate']) ? self::deserializeDateTime($data['endDate']) : null,
            !empty($data['timestamps']) ? array_map(
                function ($timestamp) {
                    return Timestamp::deserialize($timestamp);
                },
                $data['timestamps']
            ) : $defaultTimeStamps,
            !empty($data['openingHours']) ? array_map(
                function ($openingHour) {
                    return OpeningHour::deserialize($openingHour);
                },
                $data['openingHours']
            ) : []
        );
    }

    /**
     * This deserialization function takes into account old data that might be missing a timezone.
     * It will fall back to creating a DateTime object and assume Brussels.
     * If this still fails an error will be thrown.
     */
    private static function deserializeDateTime(string $dateTimeData): DateTime
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

    public function getStartDate(): ?DateTimeInterface
    {
        $timestamps = $this->getTimestamps();

        if (empty($timestamps)) {
            return $this->startDate;
        }

        $startDate = null;
        foreach ($timestamps as $timestamp) {
            if ($startDate === null || $timestamp->getStartDate() < $startDate) {
                $startDate = $timestamp->getStartDate();
            }
        }

        return $startDate;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        $timestamps = $this->getTimestamps();

        if (empty($timestamps)) {
            return $this->endDate;
        }

        $endDate = null;
        foreach ($this->getTimestamps() as $timestamp) {
            if ($endDate === null || $timestamp->getEndDate() > $endDate) {
                $endDate = $timestamp->getEndDate();
            }
        }

        return $endDate;
    }

    /**
     * @return array|OpeningHour[]
     */
    public function getOpeningHours(): array
    {
        return $this->openingHours;
    }

    /**
     * @return array|Timestamp[]
     */
    public function getTimestamps(): array
    {
        return $this->timestamps;
    }

    public function toJsonLd(): array
    {
        $jsonLd = [];

        $jsonLd['calendarType'] = $this->getType()->toNative();

        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        if ($startDate !== null) {
            $jsonLd['startDate'] = $startDate->format(DateTime::ATOM);
        }
        if ($endDate !== null) {
            $jsonLd['endDate'] = $endDate->format(DateTime::ATOM);
        }

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

        $openingHours = $this->getOpeningHours();
        if (!empty($openingHours)) {
            $jsonLd['openingHours'] = array();
            foreach ($openingHours as $openingHour) {
                $jsonLd['openingHours'][] = $openingHour->serialize();
            }
        }

        return $jsonLd;
    }

    public function sameAs(Calendar $otherCalendar): bool
    {
        return $this->toJsonLd() == $otherCalendar->toJsonLd();
    }

    public static function fromUdb3ModelCalendar(Udb3ModelCalendar $calendar): Calendar
    {
        $type = CalendarType::fromNative($calendar->getType()->toString());

        $startDate = null;
        $endDate = null;
        $timestamps = [];
        $openingHours = [];

        if ($calendar instanceof CalendarWithDateRange) {
            $startDate = $calendar->getStartDate();
            $endDate = $calendar->getEndDate();
        }

        if ($calendar instanceof CalendarWithSubEvents) {
            $timestamps = array_map(
                function (DateRange $dateRange) {
                    return Timestamp::fromUdb3ModelDateRange($dateRange);
                },
                $calendar->getSubEvents()->toArray()
            );
        }

        if ($calendar instanceof CalendarWithOpeningHours) {
            $openingHours = array_map(
                function (Udb3ModelOpeningHour $openingHour) {
                    return OpeningHour::fromUdb3ModelOpeningHour($openingHour);
                },
                $calendar->getOpeningHours()->toArray()
            );
        }

        return new self($type, $startDate, $endDate, $timestamps, $openingHours);
    }
}
