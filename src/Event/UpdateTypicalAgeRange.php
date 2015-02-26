<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\UpdateTypicalAgeRange.
 */

namespace CultuurNet\UDB3\Event;

/**
 * Provides a command to update the typicalAgeRange property.
 */
class UpdateTypicalAgeRange {

    use \CultuurNet\UDB3\UpdateTypicalAgeRangeTrait;

    public function __construct($id, $typicalAgeRange) {
      $this->id = $id;
      $this->typicalAgeRange = $typicalAgeRange;
    }

}
