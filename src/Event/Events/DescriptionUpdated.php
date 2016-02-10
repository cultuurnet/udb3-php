<?php

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

/**
 * Provides a DescriptionUpdated event.
 */
class DescriptionUpdated extends AbstractEvent implements SerializableInterface
{
    use \CultuurNet\UDB3\DescriptionUpdatedTrait;
    use BackwardsCompatibleEventTrait;

    /**
     * @param string $id
     * @param string $description
     */
    public function __construct($id, $description)
    {
        parent::__construct($id);
        $this->description = $description;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['event_id'], $data['description']);
    }
}
