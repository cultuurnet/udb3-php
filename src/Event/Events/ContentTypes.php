<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event\Events;

class ContentTypes
{
    const MAP = [
        LabelAdded::class => 'application/vnd.cultuurnet.udb3-events.event-label-added+json',
        LabelDeleted::class => 'application/vnd.cultuurnet.udb3-events.event-label-deleted+json',
        LabelsMerged::class => 'application/vnd.cultuurnet.udb3-events.event-labels-merged+json',
    ];

    /**
     * Intentionally made private.
     */
    private function __construct()
    {

    }
}
