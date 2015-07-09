<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Commands\UpdateFacilities.
 */

namespace CultuurNet\UDB3\Place\Commands;

/**
 * Provides a command to update the place facilities.
 */
class UpdateFacilities
{

    /**
     * Id that gets updated.
     * @var string
     */
    protected $id;

    /**
     * Facilities to be added.
     * @var array
     */
    protected $facilities;

    /**
     * @param string $id
     * @param array $facilities
     */
    public function __construct($id, array $facilities)
    {
        $this->id = $id;
        $this->facilities = $facilities;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getFacilities()
    {
        return $this->facilities;
    }
}
