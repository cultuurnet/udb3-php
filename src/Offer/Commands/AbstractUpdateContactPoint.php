<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\ContactPoint;

abstract class AbstractUpdateContactPoint
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
     * @param string $id
     * @param ContactPoint $contactPoint
     */
    public function __construct($id, ContactPoint $contactPoint)
    {
        $this->id = $id;
        $this->contactPoint = $contactPoint;
    }

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
