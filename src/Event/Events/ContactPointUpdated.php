<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

/**
 * Event when contactPoint was updated
 */
class ContactPointUpdated extends AbstractEvent
{
    use \CultuurNet\UDB3\ContactPointUpdatedTrait;
    use BackwardsCompatibleEventTrait;

    /**
     * @param string $id
     * @param ContactPoint $contactPoint
     */
    public function __construct($id, ContactPoint $contactPoint)
    {
        parent::__construct($id);
        $this->contactPoint = $contactPoint;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['item_id'], ContactPoint::deserialize($data['contactPoint']));
    }
}
