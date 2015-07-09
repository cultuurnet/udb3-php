<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Events\DescriptionUpdated.
 */

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Place\PlaceEvent;

/**
 * Description of DescriptionUpdated
 */
class DescriptionUpdated extends PlaceEvent
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
        return new static($data['place_id'], $data['description']);
    }
}
