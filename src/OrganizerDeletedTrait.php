<?php

/**
 * @file
 * Contains CultuurNet\UDB3\OrganizerDeletedTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Trait for the OrganizerDeletedTrait events.
 */
trait OrganizerDeletedTrait
{

    /**
     * The organizer id to delete.
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
