<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Offer\Commands\Image\AbstractUpdateImage;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Events\AbstractBookingInfoUpdated;
use CultuurNet\UDB3\Offer\Events\AbstractContactPointUpdated;
use CultuurNet\UDB3\Offer\Events\AbstractDescriptionTranslated;
use CultuurNet\UDB3\Offer\Events\AbstractDescriptionUpdated;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractOfferDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractOrganizerDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractOrganizerUpdated;
use CultuurNet\UDB3\Offer\Events\AbstractTypicalAgeRangeDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractTypicalAgeRangeUpdated;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageAdded;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageRemoved;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageUpdated;
use CultuurNet\UDB3\Offer\Events\Image\AbstractMainImageSelected;
use CultuurNet\UDB3\Offer\Events\AbstractTitleTranslated;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

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
     * @var UUID
     */
    protected $mainImageId;

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
     * Get the id of the main image if one is selected for this offer.
     *
     * @return UUID|null
     */
    protected function getMainImageId()
    {
        return $this->mainImageId;
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
     * @param Language $language
     * @param StringLiteral $title
     */
    public function translateTitle(Language $language, StringLiteral $title)
    {
        $this->apply(
            $this->createTitleTranslatedEvent($language, $title)
        );
    }

    /**
     * @param Language $language
     * @param StringLiteral $description
     */
    public function translateDescription(Language $language, StringLiteral $description)
    {
        $this->apply(
            $this->createDescriptionTranslatedEvent($language, $description)
        );
    }


    /**
     * @param string $description
     */
    public function updateDescription($description)
    {
        $this->apply(
            $this->createDescriptionUpdatedEvent($description)
        );
    }

    /**
     * @param string $typicalAgeRange
     */
    public function updateTypicalAgeRange($typicalAgeRange)
    {
        $this->apply(
            $this->createTypicalAgeRangeUpdatedEvent($typicalAgeRange)
        );
    }

    public function deleteTypicalAgeRange()
    {
        $this->apply(
            $this->createTypicalAgeRangeDeletedEvent()
        );
    }

    /**
     * @param string $organizerId
     */
    public function updateOrganizer($organizerId)
    {
        $this->apply(
            $this->createOrganizerUpdatedEvent($organizerId)
        );
    }

    /**
     * Delete the given organizer.
     *
     * @param string $organizerId
     */
    public function deleteOrganizer($organizerId)
    {
        $this->apply(
            $this->createOrganizerDeletedEvent($organizerId)
        );
    }

    /**
     * Updated the contact info.
     * @param ContactPoint $contactPoint
     */
    public function updateContactPoint(ContactPoint $contactPoint)
    {
        $this->apply(
            $this->createContactPointUpdatedEvent($contactPoint)
        );
    }

    /**
     * Updated the booking info.
     *
     * @param BookingInfo $bookingInfo
     */
    public function updateBookingInfo(BookingInfo $bookingInfo)
    {
        $this->apply(
            $this->createBookingInfoUpdatedEvent($bookingInfo)
        );
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

    /**
     * Make an existing image of the item the main image.
     *
     * @param Image $image
     */
    public function selectMainImage(Image $image)
    {
        if (!$this->containsImage($image)) {
            throw new \InvalidArgumentException('You can not select a random image to be main, it has to be added to the item.');
        }

        if ($this->mainImageId !== $image->getMediaObjectId()) {
            $this->apply(
                $this->createMainImageSelectedEvent($image)
            );
        }
    }

    /**
     * Delete the offer.
     */
    public function delete()
    {
        $this->apply(
            $this->createOfferDeletedEvent()
        );
    }

    protected function applyImageAdded(AbstractImageAdded $imageAdded)
    {
        $imageId = $imageAdded->getImage()->getMediaObjectId();
        $this->mediaObjects[] = $imageId;

        if (count($this->mediaObjects) === 1) {
            $this->mainImageId = $imageId;
        }
    }

    protected function applyImageRemoved(AbstractImageRemoved $imageRemoved)
    {
        $this->mediaObjects = array_diff(
            $this->mediaObjects,
            [$imageRemoved->getImage()->getMediaObjectId()]
        );

        $oldestImageId = reset($this->mediaObjects);
        if ($oldestImageId) {
            $this->mainImageId = $oldestImageId;
        }
    }

    protected function applyMainImageSelected(AbstractMainImageSelected $mainImageSelected)
    {
        $this->mainImageId = $mainImageSelected->getImage()->getMediaObjectId();
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
     * @param Language $language
     * @param StringLiteral $title
     * @return AbstractTitleTranslated
     */
    abstract protected function createTitleTranslatedEvent(Language $language, StringLiteral $title);

    /**
     * @param Language $language
     * @param StringLiteral $description
     * @return AbstractDescriptionTranslated
     */
    abstract protected function createDescriptionTranslatedEvent(Language $language, StringLiteral $description);

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

    /**
     * @param Image $image
     * @return AbstractMainImageSelected
     */
    abstract protected function createMainImageSelectedEvent(Image $image);

    /**
     * @return AbstractOfferDeleted
     */
    abstract protected function createOfferDeletedEvent();

    /**
     * @param string $description
     * @return AbstractDescriptionUpdated
     */
    abstract protected function createDescriptionUpdatedEvent($description);

    /**
     * @param string $typicalAgeRange
     * @return AbstractTypicalAgeRangeUpdated
     */
    abstract protected function createTypicalAgeRangeUpdatedEvent($typicalAgeRange);

    /**
     * @return AbstractTypicalAgeRangeDeleted
     */
    abstract protected function createTypicalAgeRangeDeletedEvent();

    /**
     * @param string $organizerId
     * @return AbstractOrganizerUpdated
     */
    abstract protected function createOrganizerUpdatedEvent($organizerId);

    /**
     * @param string $organizerId
     * @return AbstractOrganizerDeleted
     */
    abstract protected function createOrganizerDeletedEvent($organizerId);

    /**
     * @param ContactPoint $contactPoint
     * @return AbstractContactPointUpdated
     */
    abstract protected function createContactPointUpdatedEvent(ContactPoint $contactPoint);

    /**
     * @param BookingInfo $bookingInfo
     * @return AbstractBookingInfoUpdated
     */
    abstract protected function createBookingInfoUpdatedEvent(BookingInfo $bookingInfo);
}
