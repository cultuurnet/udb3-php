<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\BookingInfo;

abstract class AbstractUpdateBookingInfo
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
     * @param string $id
     * @param BookingInfo $bookingInfo
     */
    public function __construct($id, BookingInfo $bookingInfo)
    {
        $this->id = $id;
        $this->bookingInfo = $bookingInfo;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return BookingInfo
     */
    public function getBookingInfo()
    {
        return $this->bookingInfo;
    }
}
