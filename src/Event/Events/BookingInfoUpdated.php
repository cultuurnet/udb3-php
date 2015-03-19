<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Events\BookingInfoUpdated.
 */

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\EventEvent;

/**
 * Description of DescriptionUpdated
 */
class BookingInfoUpdated extends EventEvent
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
        return new static($data['event_id'], \CultuurNet\UDB3\BookingInfo::deserialize($data['bookingInfo']));
    }
}
