<?php

/**
 * @file
 * Contains CultuurNet\UDB3\Event\DescriptionUpdated.
 */

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\EventEvent;

/**
 * Description of DescriptionUpdated
 */
class OrganizerUpdated extends EventEvent
{
    use \CultuurNet\UDB3\OrganizerUpdatedTrait;

    /**
     * @param string $id
     * @param string $organizerId
     */
    public function __construct($id, $organizerId)
    {
        parent::__construct($id);
        $this->organizerId = $organizerId;
    }
}
