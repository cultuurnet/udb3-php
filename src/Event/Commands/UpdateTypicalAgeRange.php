<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Commands\UpdateTypicalAgeRange.
 */

namespace CultuurNet\UDB3\Event\Commands;

/**
 * Provides a command to update the typicalAgeRange property.
 */
class UpdateTypicalAgeRange
{

    use \CultuurNet\UDB3\UpdateTypicalAgeRangeTrait;

    public function __construct($id, $typicalAgeRange)
    {
        $this->id = $id;
        $this->typicalAgeRange = $typicalAgeRange;
    }
}
