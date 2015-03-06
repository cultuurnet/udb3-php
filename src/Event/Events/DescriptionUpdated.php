<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Events\DescriptionUpdated.
 */

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\EventEvent;

/**
 * Description of DescriptionUpdated
 */
class DescriptionUpdated extends EventEvent
{
    use \CultuurNet\UDB3\DescriptionUpdatedTrait;

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
