<?php

/**
 * @file
 * Contains CultuurNet\UDB3\UpdateContactPointTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Provides a trait for contact point update commands.
 */
trait UpdateContactPointTrait
{

    /**
     * Id that gets updated.
     * @var string
     */
    protected $id;

    /**
     * The contactPoint entry
     * @var ContactPoint
     */
    protected $contactPoint;

    /**
     * @return string
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * @return ContactPoint
     */
    function getContactPoint()
    {
        return $this->contactPoint;
    }
}
