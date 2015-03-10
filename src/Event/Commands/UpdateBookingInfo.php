<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Commands\UpdateBookingInfo.
 */

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\BookingInfo;

class UpdateBookingInfo {
  
    use \CultuurNet\UDB3\UpdateBookingInfoTrait;

    /**
     * @param string $id
     * @param BookingInfo $bookingInfo
     */
    public function __construct($id, BookingInfo $bookingInfo)
    {
        $this->id = $id;
        $this->bookingInfo = $bookingInfo;
    }
    
}
