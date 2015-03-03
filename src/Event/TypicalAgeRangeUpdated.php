<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\TypicalAgeRangeUpdated.
 */

namespace CultuurNet\UDB3\Event;

/**
 * Description of TypicalAgeRangeUpdated
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
}
