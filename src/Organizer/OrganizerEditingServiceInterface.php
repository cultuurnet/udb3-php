<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Organizer;

use CultuurNet\UDB3\Title;
use ValueObjects\Identity\UUID;

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
     * @param $organizerId
     * @param UUID $labelId
     */
    public function addLabel($organizerId, UUID $labelId);

    /**
     * @param $organizerId
     * @param UUID $labelId
     */
    public function removeLabel($organizerId, UUID $labelId);

    /**
     * @param $id
     */
    public function delete($id);
}
