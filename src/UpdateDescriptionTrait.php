<?php

/**
 * @file
 * Contains CultuurNet\UDB3\UpdateDescription.
 */

namespace CultuurNet\UDB3;

/**
 * Provides a trait for description update commands.
 */
trait UpdateDescriptionTrait {

  /**
   * Id that gets updated.
   * @var string
   */
  protected $id;

  /**
   * Description to be added.
   * @var string
   */
  protected $description;

  /**
   * @return string
   */
  function getId() {
    return $this->id;
  }

  /**
   * @return string
   */
  function getDescription() {
    return $this->description;
  }

}
