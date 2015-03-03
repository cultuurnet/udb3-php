<?php

/**
 * @file
 * Contains CultuurNet\UDB3\CalendarBase.
 */

namespace CultuurNet\UDB3;

/**
 * Utility class for calendars.
 */
class CalendarUtility
{

    /**
     * Regular expression to validate the date format.
     */
    const REGEX_DATE = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/';

    /**
     * Regular expression for matching a ISO8601 formatted time (xml primitive datatype xs:time).
     *
     * Source: "Regular Expressions Cookbook", ISBN-13 978-0-596-52068-7.
     */
    const ISO8601_REGEX_TIME = '^(2[0-3]|[0-1][0-9]):([0-5][0-9]):([0-5][0-9])(\.[0-9]+)??(Z|[+-](?:2[0-3]|[0-1][0-9]):[0-5][0-9])?$';

    /**
     * Validate a given date.
     * @param string $value
     *   Date to validate.
     *
     * @throws Exception
     */
    public static function validateDate($value)
    {
        if (!preg_match(self::REGEX_DATE, $value)) {
            throw new \UnexpectedValueException('Invalid date: ' . $value . '. The format should be Y-m-d');
        }
    }

    /**
     * Validate a given time.
     * @param string $value
     *   Time to validate.
     * @throws Exception
     */
    public static function validateTime($value)
    {
        if (!preg_match('/' . self::ISO8601_REGEX_TIME . '/', $value)) {
            throw new \UnexpectedValueException('Invalid time: ' . $value . '. The format should be h:i:s');
        }
    }
}
