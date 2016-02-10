<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Offer\Events\AbstractEvent;

class OrganizerUpdated extends AbstractEvent
{
    use \CultuurNet\UDB3\OrganizerUpdatedTrait;
    use BackwardsCompatibleEventTrait;

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
        return new static($data['item_id'], $data['organizerId']);
    }
}
