<?php

namespace CultuurNet\UDB3\Offer\Item;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Offer\Commands\Image\AbstractUpdateImage;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageAdded;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageRemoved;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageUpdated;
use CultuurNet\UDB3\Offer\Item\Events\ItemCreated;
use CultuurNet\UDB3\Offer\Item\Events\LabelAdded;
use CultuurNet\UDB3\Offer\Item\Events\LabelDeleted;
use CultuurNet\UDB3\Offer\Offer;
use Offer\Item\Events\ImageAdded;
use Offer\Item\Events\ImageRemoved;
use Offer\Item\Events\ImageUpdated;

class Item extends Offer
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @param ItemCreated $created
     */
    protected function applyItemCreated(ItemCreated $created)
    {
        $this->id = $created->getItemId();
    }

    /**
     * @param Label $label
     * @return LabelAdded
     */
    protected function createLabelAddedEvent(Label $label)
    {
        return new LabelAdded($this->id, $label);
    }

    /**
     * @param Label $label
     * @return LabelDeleted
     */
    protected function createLabelDeletedEvent(Label $label)
    {
        return new LabelDeleted($this->id, $label);
    }

    protected function createImageAddedEvent(Image $image)
    {
        return new ImageAdded($this->id, $image);
    }

    protected function createImageRemovedEvent(Image $image)
    {
        return new ImageRemoved($this->id, $image);
    }

    protected function createImageUpdatedEvent(
        AbstractUpdateImage $updateImageCommand
    ) {
        return new ImageUpdated(
            $this->id,
            $updateImageCommand->getMediaObjectId(),
            $updateImageCommand->getDescription(),
            $updateImageCommand->getCopyrightHolder()
        );
    }


    /**
     * @return mixed
     */
    public function getAggregateRootId()
    {
        return $this->id;
    }
}
