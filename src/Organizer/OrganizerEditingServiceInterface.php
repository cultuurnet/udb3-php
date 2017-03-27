<?php

namespace CultuurNet\UDB3\Organizer;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Title;
use ValueObjects\Web\Url;

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
     * @param Url $website
     * @return void
     */
    public function updateWebsite($organizerId, Url $website);

    /**
     * @param string $organizerId
     * @param Title $title
     * @return void
     */
    public function updateTitle($organizerId, Title $title);

    /**
     * @param string $organizerId
     * @param Address $address
     * @return void
     */
    public function updateAddress($organizerId, Address $address);

    /**
     * @param string $organizerId
     * @param ContactPoint $contactPoint
     * @return void
     */
    public function updateContactPoint($organizerId, ContactPoint $contactPoint);

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
