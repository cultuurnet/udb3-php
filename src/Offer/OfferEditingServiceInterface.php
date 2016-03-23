<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Media\Image;
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

    /**
     * @param $id
     * @param Image $image
     *
     * @return string
     *  The command id for this action.
     */
    public function selectMainImage($id, Image $image);
}
