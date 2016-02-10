<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Offer\Events\AbstractEvent;

/**
 * Event for typical age range updates.
 */
class TypicalAgeRangeUpdated extends AbstractEvent
{
    use \CultuurNet\UDB3\TypicalAgeRangeUpdatedTrait;
    use BackwardsCompatibleEventTrait;

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
        return new static($data['item_id'], $data['typicalAgeRange']);
    }
}
