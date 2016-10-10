<?php

namespace CultuurNet\UDB3\Organizer\ReadModel\JSONLD;

use CultuurNet\UDB3\Organizer\OrganizerProjectedToJSONLD;
use CultuurNet\UDB3\ReadModel\DocumentEventFactory;

class EventFactory implements DocumentEventFactory
{
    /**
     * @param string $id
     * @return OrganizerProjectedToJSONLD
     */
    public function createEvent($id)
    {
        return new OrganizerProjectedToJSONLD($id);
    }
}
