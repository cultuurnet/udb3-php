<?php

namespace CultuurNet\UDB3\Place\Commands;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Commands\AbstractTranslateDescription;
use CultuurNet\UDB3\Offer\Commands\AbstractTranslateTitle;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

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


    public function createAddImageCommand($id, Image $image)
    {
        return new AddImage($id, $image);
    }

    public function createRemoveImageCommand($id, Image $image)
    {
        return new RemoveImage($id, $image);
    }

    public function createUpdateImageCommand(
        $id,
        UUID $mediaObjectId,
        StringLiteral $description,
        StringLiteral $copyrightHolder
    ) {
        return new UpdateImage(
            $id,
            $mediaObjectId,
            $description,
            $copyrightHolder
        );
    }

    /**
     * @param $id
     * @param Language $language
     * @param StringLiteral $title
     * @return AbstractTranslateTitle
     */
    public function createTranslateTitleCommand($id, Language $language, StringLiteral $title)
    {
        return new TranslateTitle($id, $language, $title);
    }

    /**
     * @param $id
     * @param Language $language
     * @param StringLiteral $description
     * @return AbstractTranslateDescription
     */
    public function createTranslateDescriptionCommand($id, Language $language, StringLiteral $description)
    {
        return new TranslateDescription($id, $language, $description);
    }
}
