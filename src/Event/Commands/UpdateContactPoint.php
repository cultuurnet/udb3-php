<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\Commands\UpdateContactPoint.
 */

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\ContactPoint;

/**
 * Provides a command to update the contact point.
 */
class UpdateContactPoint
{

    use \CultuurNet\UDB3\UpdateContactPointTrait;

    /**
     * @param string $id
     * @param ContactPoint $contactPoint
     */
    public function __construct($id, ContactPoint $contactPoint)
    {
        $this->id = $id;
        $this->contactPoint = $contactPoint;
    }
}
