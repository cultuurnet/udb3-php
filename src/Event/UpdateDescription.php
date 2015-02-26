<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\UpdateDescription.
 */

namespace CultuurNet\UDB3\Event;

/**
 * Provides a command to update the description for the main language.
 */
class UpdateDescription {

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

  public function __construct($id, $description) {
    $this->id = $id;
    $this->description = $description;
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
  function getDescription() {
    return $this->description;
  }

}
