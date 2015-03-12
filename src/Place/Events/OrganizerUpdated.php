<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Events\OrganizerUpdated.
 */

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Place\PlaceEvent;

/**
 * Description of DescriptionUpdated
 */
class OrganizerUpdated extends PlaceEvent
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
