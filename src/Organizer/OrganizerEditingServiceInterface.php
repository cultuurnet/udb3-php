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
     * @param array $addresses
     * @param array $phones
     * @param array $emails
     * @param array $urls
     *
     * @return string $organizerId
     */
    public function create(Title $title, array $addresses, array $phones, array $emails, array $urls);

    /**
     * @param $id
     */
    public function delete($id);
}
