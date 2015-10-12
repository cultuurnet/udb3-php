<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Organizer;

use CultuurNet\UDB3\Title;

interface OrganizerEditingServiceInterface
{

    /**
     * @param Title $title
     * @param array $adresses
     * @param array $phones
     * @param array $emails
     * @param array $urls
     *
     * @return string $organizerId
     */
    public function createOrganizer(Title $title, array $addresses, array $phones, array $emails, array $urls);
}
