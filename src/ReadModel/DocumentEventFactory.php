<?php

namespace CultuurNet\UDB3\Event\ReadModel;

use CultuurNet\UDB3\Offer\Events\AbstractEvent;

interface DocumentEventFactory
{
    /**
     * @param $id
     * @return AbstractEvent
     */
    public function createEvent($id);
}
