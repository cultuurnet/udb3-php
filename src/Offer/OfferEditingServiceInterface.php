<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use ValueObjects\String\String;

interface OfferEditingServiceInterface
{
    /**
     * @param $id
     * @param Label $label
     */
    public function addLabel($id, Label $label);

    /**
     * @param $id
     * @param Label $label
     */
    public function deleteLabel($id, Label $label);

    /**
     * @param $id
     * @param Language $language
     * @param String $title
     */
    public function translateTitle($id, Language $language, String $title);

    /**
     * @param $id
     * @param Language $language
     * @param String $description
     */
    public function translateDescription($id, Language $language, String $description);
}
