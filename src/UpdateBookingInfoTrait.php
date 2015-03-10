<?php

/**
 * @file
 * Contains CultuurNet\UDB3\UpdateBookingInfoTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Provides a trait for booking info update commands.
 */
trait UpdateBookingInfoTrait
{

    /**
     * Id that gets updated.
     * @var string
     */
    protected $id;

    /**
     * The bookingInfo entry
     * @var BookingInfo
     */
    protected $bookingInfo;

    /**
     * @return string
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * @return BookingInfo
     */
    function getBookingInfo()
    {
        return $this->bookingInfo;
    }
}

