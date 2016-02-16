<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Offer\Commands\Image\AbstractAddImage;
use CultuurNet\UDB3\Offer\Commands\Image\AbstractRemoveImage;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;
use CultuurNet\UDB3\Language;

interface OfferCommandFactoryInterface
{
    /**
     * @param $id
     * @param Label $label
     * @return AbstractAddLabel
     */
    public function createAddLabelCommand($id, Label $label);

    /**
     * @param $id
     * @param Label $label
     * @return AbstractDeleteLabel
     */
    public function createDeleteLabelCommand($id, Label $label);

    /**
     * @param $id
     * @param Image $image
     * @return AbstractAddImage
     */
    public function createAddImageCommand($id, Image $image);

    /**
     * @param $id
     * @param Image $image
     * @return AbstractRemoveImage
     */
    public function createRemoveImageCommand($id, Image $image);

    /**
     * @param $id
     * @param UUID $mediaObjectId
     * @param StringLiteral $description
     * @param StringLiteral $copyrightHolder
     */
    public function createUpdateImageCommand(
        $id,
        UUID $mediaObjectId,
        StringLiteral $description,
        StringLiteral $copyrightHolder
    );

    /**
     * @param $id
     * @param Language $language
     * @param StringLiteral $title
     * @return mixed
     */
    public function createTranslateTitleCommand($id, Language $language, StringLiteral $title);

    /**
     * @param $id
     * @param Language $language
     * @param StringLiteral $description
     * @return mixed
     */
    public function createTranslateDescriptionCommand($id, Language $language, StringLiteral $description);
}
