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

    /**
     * @var string
     */
    protected $id;

    /**
     * The new typical age range.
     * @var string
     */
    protected $typicalAgeRange;

    public function __construct($id, $typicalAgeRange) {
      $this->id = $id;
      $this->typicalAgeRange = $typicalAgeRange;
    }

    /**
     * @return string
     */
    function getId() {
      return $this->id;
    }

    /**
     * @return string
     */
    function getTypicalAgeRange() {
      return $this->typicalAgeRange;
    }

}
