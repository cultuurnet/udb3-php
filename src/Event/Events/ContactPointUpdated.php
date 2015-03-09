<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Events\ContactPointUpdated.
 */

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\EventEvent;

/**
 * Event when contactPoint was updated
 */
class ContactPointUpdated extends EventEvent
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
        return new static($data['event_id'], ContactPoint::deserialize($data['contactPoint']));
    }
}
