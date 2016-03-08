<?php

namespace CultuurNet\UDB3\Event\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Event\EventEvent;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

class EventProjectedToJSONLD extends AbstractEvent implements SerializableInterface
{
    /**
     * @deprecated
     *  Use getItemId().
     * @return string
     */
    public function getEventId()
    {
        return $this->getItemId();
    }
}
