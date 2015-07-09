<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Events\OrganizerUpdated.
 */

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\EventEvent;

/**
 * Description of DescriptionUpdated
 */
class OrganizerUpdated extends EventEvent
{
    use \CultuurNet\UDB3\OrganizerUpdatedTrait;

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
        return new static($data['place_id'], $data['organizerId']);
    }
}
