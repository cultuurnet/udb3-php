<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Offer\Commands\AbstractAddLabel;
use CultuurNet\UDB3\Offer\Commands\AbstractDeleteLabel;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class EventCommandFactory implements OfferCommandFactoryInterface
{
    /**
     * @param $id
     * @param Label $label
     * @return AbstractAddLabel
     */
    public function createAddLabelCommand($id, Label $label)
    {
        return new AddLabel($id, $label);
    }

    /**
     * @param $id
     * @param Label $label
     * @return AbstractDeleteLabel
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
}
