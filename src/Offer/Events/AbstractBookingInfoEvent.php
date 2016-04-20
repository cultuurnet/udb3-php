<?php

namespace CultuurNet\UDB3\Offer\Events;
use \CultuurNet\UDB3\BookingInfo;

abstract class AbstractBookingInfoEvent extends AbstractEvent
{
    protected $bookingInfo;
    /**
     * @param string $id
     * @param BookingInfo $bookingInfo
     */
    public function __construct($id, BookingInfo $bookingInfo)
    {
        parent::__construct($id);
        $this->bookingInfo = $bookingInfo;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['item_id'], BookingInfo::deserialize($data['bookingInfo']));
    }
}
