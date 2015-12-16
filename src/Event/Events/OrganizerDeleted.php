<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Events\OrganizerDeleted.
 */

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\EventEvent;

/**
 * Organizer deleted event
 */
class OrganizerDeleted extends EventEvent
{
    use \CultuurNet\UDB3\OrganizerDeletedTrait;

    /**
     * @param string $id
     * @param string $organizerId
     */
    public function __construct($id, $organizerId)
    {
        parent::__construct($id);
        $this->organizerId = $organizerId;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['event_id'], $data['organizerId']);
    }
}
