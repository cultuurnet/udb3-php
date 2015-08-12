<?php

namespace CultuurNet\UDB3\Event\ReadModel;

use CultuurNet\UDB3\Event\EventEvent;

interface DocumentEventFactory
{
    /**
     * @param $id
     * @return EventEvent
     */
    public function createEvent($id);
}
