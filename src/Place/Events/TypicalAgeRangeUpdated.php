<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Events\TypicalAgeRangeUpdated.
 */

namespace CultuurNet\UDB3\Place\Events;

use CultuurNet\UDB3\Place\PlaceEvent;

/**
 * Event for typical age range updates.
 */
class TypicalAgeRangeUpdated extends PlaceEvent
{

    use \CultuurNet\UDB3\TypicalAgeRangeUpdatedTrait;

    /**
     * @param string $id
     * @param string $typicalAgeRange
     */
    public function __construct($id, $typicalAgeRange)
    {
        parent::__construct($id);
        $this->typicalAgeRange = $typicalAgeRange;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['place_id'], $data['typicalAgeRange']);
    }
}
