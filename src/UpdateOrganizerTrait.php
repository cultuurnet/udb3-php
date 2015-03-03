<?php

/**
 * @file
 * Contains CultuurNet\UDB3\UpdateOrganizerTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Provides a trait for organizer update commands.
 */
trait UpdateOrganizerTrait {

  /**
   * Id that gets updated.
   * @var string
   */
  protected $id;

  /**
   * OrganizerId to be set
   * @var string
   */
  protected $organizerId;

  /**
   * @return string
   */
  function getId() {
    return $this->id;
  }

  /**
   * @return string
   */
  function getOrganizerId() {
    return $this->organizerId;
  }

}
