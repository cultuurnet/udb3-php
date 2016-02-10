<?php

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

/**
 * Description of DescriptionUpdated
 */
class BookingInfoUpdated extends AbstractEvent implements SerializableInterface
{
    use \CultuurNet\UDB3\BookingInfoUpdatedTrait;
    use BackwardsCompatibleEventTrait;

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
        return new static($data['item_id'], \CultuurNet\UDB3\BookingInfo::deserialize($data['bookingInfo']));
    }
}
