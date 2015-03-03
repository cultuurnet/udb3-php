<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\TypicalAgeRangeUpdated.
 */

namespace CultuurNet\UDB3\Place;

/**
 * Description of TypicalAgeRangeUpdated
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
}
