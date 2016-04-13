<?php

namespace CultuurNet\UDB3\Organizer\Events;

class ContentTypes
{
    /**
     * Intentionally made private.
     */
    private function __construct()
    {

    }

    /**
     * @return array
     *
     * @todo once we upgrade to PHP 5.6+ this can be moved to a constant.
     */
    public static function map()
    {
        return [
            OrganizerCreated::class => 'application/vnd.cultuurnet.udb3-events.organizer-created+json',
            OrganizerImportedFromUDB2::class => 'application/vnd.cultuurnet.udb3-events.organizer-imported-from-udb2+json',
            OrganizerUpdatedFromUDB2::class => 'application/vnd.cultuurnet.udb3-events.organizer-updated-from-udb2+json'
        ];
    }
}
