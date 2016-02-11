<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Offer\Commands\Image\AbstractUpdateImage;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelDeleted;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageAdded;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageRemoved;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageUpdated;

abstract class Offer extends EventSourcedAggregateRoot
{
    /**
     * @var LabelCollection
     */
    protected $labels;

    /**
     * @var UUID[]
     */
    protected $mediaObjects = [];

    /**
     * Offer constructor.
     */
    public function __construct()
    {
        $this->resetLabels();
    }

    /**
     * @return LabelCollection
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param Label $label
     */
    public function addLabel(Label $label)
    {
        if (!$this->labels->contains($label)) {
            $this->apply(
                $this->createLabelAddedEvent($label)
            );
        }
    }

    /**
     * @param Label $label
     */
    public function deleteLabel(Label $label)
    {
        if ($this->labels->contains($label)) {
            $this->apply(
                $this->createLabelDeletedEvent($label)
            );
        }
    }

    /**
     * @param AbstractLabelAdded $labelAdded
     */
    protected function applyLabelAdded(AbstractLabelAdded $labelAdded)
    {
        $newLabel = $labelAdded->getLabel();

        if (!$this->labels->contains($newLabel)) {
            $this->labels = $this->labels->with($newLabel);
        }
    }

    /**
     * @param AbstractLabelDeleted $labelDeleted
     */
    protected function applyLabelDeleted(AbstractLabelDeleted $labelDeleted)
    {
        $this->labels = $this->labels->without(
            $labelDeleted->getLabel()
        );
    }

    protected function resetLabels()
    {
        $this->labels = new LabelCollection();
    }

    /**
     * @param Image $image
     * @return boolean
     */
    private function containsImage(Image $image)
    {
        $equalImages = array_filter(
            $this->mediaObjects,
            function ($existingMediaObjectId) use ($image) {
                return $image
                    ->getMediaObjectId()
                    ->sameValueAs($existingMediaObjectId);
            }
        );

        return !empty($equalImages);
    }

    /**
     * Add a new image.
     *
     * @param Image $image
     */
    public function addImage(Image $image)
    {
        if (!$this->containsImage($image)) {
            $this->apply(
                $this->createImageAddedEvent($image)
            );
        }
    }

    /**
     * @param AbstractUpdateImage $updateImageCommand
     */
    public function updateImage(AbstractUpdateImage $updateImageCommand)
    {
        $this->apply(
            $this->createImageUpdatedEvent($updateImageCommand)
        );
    }

    /**
     * Remove an image.
     *
     * @param Image $image
     */
    public function removeImage(Image $image)
    {
        if ($this->containsImage($image)) {
            $this->apply(
                $this->createImageRemovedEvent($image)
            );
        }
    }

    protected function applyImageAdded(AbstractImageAdded $imageAdded)
    {
        $this->mediaObjects[] = $imageAdded->getImage()->getMediaObjectId();
    }

    protected function applyImageRemoved(AbstractImageRemoved $imageRemoved)
    {
        $this->mediaObjects = array_diff(
            $this->mediaObjects,
            [$imageRemoved->getImage()->getMediaObjectId()]
        );
    }

    /**
     * @param Label $label
     * @return AbstractLabelAdded
     */
    abstract protected function createLabelAddedEvent(Label $label);

    /**
     * @param Label $label
     * @return AbstractLabelDeleted
     */
    abstract protected function createLabelDeletedEvent(Label $label);

    /**
     * @param Image $image
     * @return AbstractImageAdded
     */
    abstract protected function createImageAddedEvent(Image $image);

    /**
     * @param Image $image
     * @return AbstractImageRemoved
     */
    abstract protected function createImageRemovedEvent(Image $image);

    /**
     * @param AbstractUpdateImage $updateImageCommand
     * @return AbstractImageUpdated
     */
    abstract protected function createImageUpdatedEvent(
        AbstractUpdateImage $updateImageCommand
    );
}
