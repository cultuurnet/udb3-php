<?php

namespace CultuurNet\UDB3\Organizer;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Title;
use ValueObjects\Web\Url;

interface OrganizerEditingServiceInterface
{
    /**
     * @param Language $mainLanguage
     * @param Url $website
     * @param Title $title
     * @param Address|null $address
     * @param ContactPoint|null $contactPoint
     * @return string $organizerId
     */
    public function create(
        Language $mainLanguage,
        Url $website,
        Title $title,
        Address $address = null,
        ContactPoint $contactPoint = null
    );

    /**
     * @param string $organizerId
     * @param Url $website
     * @return mixed
     */
    public function updateWebsite($organizerId, Url $website);

    /**
     * @param string $organizerId
     * @param Title $title
     * @param Language $language
     * @return mixed
     */
    public function updateTitle($organizerId, Title $title, Language $language);

    /**
     * @param string $organizerId
     * @param Address $address
     * @return mixed
     */
    public function updateAddress($organizerId, Address $address);

    /**
     * @param string $organizerId
     * @param ContactPoint $contactPoint
     * @return mixed
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
