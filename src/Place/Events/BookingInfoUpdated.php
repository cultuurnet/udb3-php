<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Events\BookingInfoUpdated.
 */

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Place\PlaceEvent;

/**
 * Description of BookingInfoUpdated
 */
class BookingInfoUpdated extends PlaceEvent
{
    use \CultuurNet\UDB3\BookingInfoUpdatedTrait;

    /**
     * @param string $id
     * @param Object $bookingInfo
     */
    public function __construct($id, $bookingInfo)
    {
        parent::__construct($id);
        $this->bookingInfo = $bookingInfo;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['place_id'], $data['bookingInfo']);
    }
}
