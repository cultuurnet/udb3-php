<?php

namespace CultuurNet\UDB3\Place\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

class PlaceProjectedToJSONLD extends AbstractEvent implements SerializableInterface
{
    /**
     * @deprecated
     *  Use getItemId().
     * @return string
     */
    public function getId()
    {
        return $this->getItemId();
    }
}
