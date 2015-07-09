<?php

/**
 * @file
 * Contains CultuurNet\UDB3\DeleteOrganizerTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Provides a trait for deleting organizer commands.
 */
trait DeleteOrganizerTrait
{

    /**
     * Id that gets updated.
     * @var string
     */
    protected $id;

    /**
     * Organizer id to delete
     * @var string
     */
    protected $organizerId;

    /**
     * @return string
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    function getOrganizerId()
    {
        return $this->organizerId;
    }
}
