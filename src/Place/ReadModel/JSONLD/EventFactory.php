<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Place\ReadModel\JSONLD;

use CultuurNet\UDB3\Event\ReadModel\DocumentEventFactory;
use CultuurNet\UDB3\Place\PlaceEvent;
use CultuurNet\UDB3\Place\PlaceProjectedToJSONLD;

class EventFactory implements DocumentEventFactory
{
    /**
     * @inheritdoc
     */
    public function createEvent($id)
    {
        return new PlaceProjectedToJSONLD($id);
    }
}
