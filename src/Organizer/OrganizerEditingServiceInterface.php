<?php

namespace CultuurNet\UDB3\Organizer;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Title;
use ValueObjects\Web\Url;
use ValueObjects\Identity\UUID;

interface OrganizerEditingServiceInterface
{
    /**
     * @param Url $website
     * @param Title $title
     * @param Address|null $address
     * @param ContactPoint|null $contactPoint
     * @return string $organizerId
     */
    public function create(Url $website, Title $title, Address $address = null, ContactPoint $contactPoint = null);

    /**
     * @param string $organizerId
     * @param Label $label
     * @return
     */
    public function addLabel($organizerId, Label $label);

    /**
     * @param string $organizerId
     * @param Label $label
     * @return
     */
    public function removeLabel($organizerId, Label $label);

    /**
     * @param $id
     */
    public function delete($id);
}
