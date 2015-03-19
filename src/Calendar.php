<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Calendar.
 */

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\Timestamp;
use CultuurNet\UDB3\CalendarInterface;

/**
 * a Calendar for events and places.
 */
class Calendar implements CalendarInterface, JsonLdSerializableInterface
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
     * Constructor.
     */
    public function __construct($calendarType, $startDate = '', $endDate = '', $timestamps = array(), $openingHours = array())
    {
        if ($calendarType != self::PERMANENT && $calendarType != self::MULTIPLE && $calendarType != self::PERIODIC && $calendarType != self::SINGLE) {
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
     * Get current calendar type.
     * @return string
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
        return [
          'type' => $this->getType(),
          'startDate' => $this->startDate,
          'endDate' => $this->endDate,
          'timestamps' => $this->timestamps,
          'openingHours' => $this->openingHours,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['type'], $data['startDate'], $data['endDate'], $data['timestamps'], $data['openingHours']
        );
    }

    /**
     * Get the start date
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
     * Get the opening hours
     */
    public function getOpeningHours()
    {
        return $this->openingHours;
    }

    /**
     * Get the Timestamps
     * @return type
     */
    public function getTimestamps()
    {
        return $this->timestamps;
    }

    /**
     * Return the jsonLD version of a calendar.
     */
    public function toJsonLd() {

        $jsonLd = [];

        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        $jsonLD['calendarType'] = $this->getType();
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
            $jsonLD['openingHours'] = array();
            foreach ($openingHours as $openingHour) {
                $schedule = array('dayOfWeek' => $openingHour->dayOfWeek);
                if (!empty($openingHour->opens)) {
                    $schedule['opens'] = $openingHour->opens;
                }
                if (!empty($openingHour->closes)) {
                    $schedule['closes'] = $openingHour->closes;
                }
                $jsonLD['openingHours'][] = $schedule;
            }
        }

        return $jsonLD;

    }

}
