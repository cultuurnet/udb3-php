<?php

namespace CultuurNet\UDB3\Offer\Events;

use \CultuurNet\UDB3\BookingInfo;

abstract class AbstractBookingInfoEvent extends AbstractEvent
{
    /**
     * @var BookingInfo
     */
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
     * @return BookingInfo
     */
    public function getBookingInfo()
    {
        return $this->bookingInfo;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'bookingInfo' => $this->bookingInfo->serialize(),
        );
    }

    /**
     * @param array $data
     * @return AbstractBookingInfoEvent
     */
    public static function deserialize(array $data)
    {
        return new static($data['item_id'], BookingInfo::deserialize($data['bookingInfo']));
    }
}
