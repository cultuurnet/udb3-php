<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Events\ContactPointUpdated.
 */

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Place\PlaceEvent;

/**
 * Event when contactPoint was updated
 */
class ContactPointUpdated extends PlaceEvent
{
    use \CultuurNet\UDB3\ContactPointUpdatedTrait;

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
        return new static($data['place_id'], ContactPoint::deserialize($data['contactPoint']));
    }
}
