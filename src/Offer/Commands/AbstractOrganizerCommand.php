<?php

namespace CultuurNet\UDB3\Offer\Commands;

abstract class AbstractOrganizerCommand
{
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
     * @param string $id
     * @param string $organizerId
     */
    public function __construct($id, $organizerId)
    {
        $this->id = $id;
        $this->organizerId = $organizerId;
    }

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
