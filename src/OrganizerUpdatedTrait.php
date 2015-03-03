<?php

/**
 * @file
 * Contains CultuurNet\UDB3\OrganizerUpdatedTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Trait for the OrganizerUpdatedTrait events.
 */
trait OrganizerUpdatedTrait
{

    /**
     * The new organizer id
     * @var string
     */
    protected $organizerId;

    /**
     * @return string
     */
    public function getOrganizerId()
    {
        return $this->organizerId;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return parent::serialize() + array(
          'organizerId' => $this->organizerId,
        );
    }
}
