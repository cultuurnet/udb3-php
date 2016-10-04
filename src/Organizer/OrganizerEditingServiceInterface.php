<?php

namespace CultuurNet\UDB3\Organizer;

use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Title;
use ValueObjects\Web\Url;
use ValueObjects\Identity\UUID;

interface OrganizerEditingServiceInterface
{

    /**
     * @param Url $website
     * @param Title $title
     * @param array $addresses
     * @param ContactPoint $contactPoint
     * @return string $organizerId
     */
    public function create(Url $website, Title $title, array $addresses, ContactPoint $contactPoint);

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
