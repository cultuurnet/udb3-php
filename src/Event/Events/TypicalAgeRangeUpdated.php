<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Events\TypicalAgeRangeUpdated.
 */

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\EventEvent;

/**
 * Event for typical age range updates.
 */
class TypicalAgeRangeUpdated extends EventEvent
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
