<?php

namespace CultuurNet\UDB3\Place\Commands;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Commands\AbstractTranslateDescription;
use CultuurNet\UDB3\Offer\Commands\AbstractTranslateTitle;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;
use ValueObjects\String\String;

class PlaceCommandFactory implements OfferCommandFactoryInterface
{
    /**
     * @param $id
     * @param Label $label
     * @return AddLabel
     */
    public function createAddLabelCommand($id, Label $label)
    {
        return new AddLabel($id, $label);
    }

    /**
     * @param $id
     * @param Label $label
     * @return DeleteLabel
     */
    public function createDeleteLabelCommand($id, Label $label)
    {
        return new DeleteLabel($id, $label);
    }

    /**
     * @param $id
     * @param Language $language
     * @param String $title
     * @return AbstractTranslateTitle
     */
    public function createTranslateTitleCommand($id, Language $language, String $title)
    {
        return new TranslateTitle($id, $language, $title);
    }

    /**
     * @param $id
     * @param Language $language
     * @param String $description
     * @return AbstractTranslateDescription
     */
    public function createTranslateDescriptionCommand($id, Language $language, String $description)
    {
        return new TranslateDescription($id, $language, $description);
    }
}
