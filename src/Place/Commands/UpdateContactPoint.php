<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Place\Commands\UpdateContactPoint.
 */

namespace CultuurNet\UDB3\Place\Commands;

use CultuurNet\UDB3\ContactPoint;

/**
 * Provides a command to update the contact info.
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
