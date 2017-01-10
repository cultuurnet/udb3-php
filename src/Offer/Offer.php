<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\EventSourcing\EventSourcedAggregateRoot;
use CultureFeed_Cdb_Item_Base;
use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Media\ImageCollection;
use CultuurNet\UDB3\Offer\Commands\Image\AbstractUpdateImage;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Events\AbstractBookingInfoUpdated;
use CultuurNet\UDB3\Offer\Events\AbstractContactPointUpdated;
use CultuurNet\UDB3\Offer\Events\AbstractDescriptionTranslated;
use CultuurNet\UDB3\Offer\Events\AbstractDescriptionUpdated;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelRemoved;
use CultuurNet\UDB3\Offer\Events\AbstractOfferDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractOrganizerDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractOrganizerUpdated;
use CultuurNet\UDB3\Offer\Events\AbstractPriceInfoUpdated;
use CultuurNet\UDB3\Offer\Events\AbstractTypicalAgeRangeDeleted;
use CultuurNet\UDB3\Offer\Events\AbstractTypicalAgeRangeUpdated;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageAdded;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageRemoved;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImagesEvent;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImagesImportedFromUDB2;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImagesUpdatedFromUDB2;
use CultuurNet\UDB3\Offer\Events\Image\AbstractImageUpdated;
use CultuurNet\UDB3\Offer\Events\Image\AbstractMainImageSelected;
use CultuurNet\UDB3\Offer\Events\AbstractTitleTranslated;
use CultuurNet\UDB3\Offer\Events\Moderation\AbstractApproved;
use CultuurNet\UDB3\Offer\Events\Moderation\AbstractFlaggedAsDuplicate;
use CultuurNet\UDB3\Offer\Events\Moderation\AbstractFlaggedAsInappropriate;
use CultuurNet\UDB3\Offer\Events\Moderation\AbstractPublished;
use CultuurNet\UDB3\Offer\Events\Moderation\AbstractRejected;
use CultuurNet\UDB3\PriceInfo\PriceInfo;
use Exception;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

abstract class Offer extends EventSourcedAggregateRoot
{
    const DUPLICATE_REASON = 'duplicate';
    const INAPPROPRIATE_REASON = 'inappropriate';

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
     * @var string
     *
     * Organizer ids can come from UDB2 which does not strictly use UUIDs.
     */
    protected $organizerId;

    /**
     * @var WorkflowStatus
     */
    protected $workflowStatus;

    /**
     * @var StringLiteral|null
     */
    protected $rejectedReason;

    /**
     * @var PriceInfo
     */
    protected $priceInfo;

    /**
     * Offer constructor.
     */
    public function __construct()
    {
        $this->labels = new LabelCollection();
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
    public function removeLabel(Label $label)
    {
        if ($this->labels->contains($label)) {
            $this->apply(
                $this->createLabelRemovedEvent($label)
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
        if ($this->organizerId !== $organizerId) {
            $this->apply(
                $this->createOrganizerUpdatedEvent($organizerId)
            );
        }
    }

    /**
     * Delete the given organizer.
     *
     * @param string $organizerId
     */
    public function deleteOrganizer($organizerId)
    {
        if ($this->organizerId === $organizerId) {
            $this->apply(
                $this->createOrganizerDeletedEvent($organizerId)
            );
        }
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
     * @param PriceInfo $priceInfo
     */
    public function updatePriceInfo(PriceInfo $priceInfo)
    {
        if (is_null($this->priceInfo) || $priceInfo->serialize() !== $this->priceInfo->serialize()) {
            $this->apply(
                $this->createPriceInfoUpdatedEvent($priceInfo)
            );
        }
    }

    /**
     * @param AbstractPriceInfoUpdated $priceInfoUpdated
     */
    protected function applyPriceInfoUpdated(AbstractPriceInfoUpdated $priceInfoUpdated)
    {
        $this->priceInfo = $priceInfoUpdated->getPriceInfo();
    }

    /**
     * @param AbstractLabelAdded $labelAdded
     */
    protected function applyLabelAdded(AbstractLabelAdded $labelAdded)
    {
        $this->labels = $this->labels->with($labelAdded->getLabel());
    }

    /**
     * @param AbstractLabelRemoved $labelRemoved
     */
    protected function applyLabelRemoved(AbstractLabelRemoved $labelRemoved)
    {
        $this->labels = $this->labels->without($labelRemoved->getLabel());
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

    /**
     * @param CultureFeed_Cdb_Item_Base $cdbItem
     */
    protected function importWorkflowStatus(CultureFeed_Cdb_Item_Base $cdbItem)
    {
        try {
            $workflowStatus = WorkflowStatus::fromNative($cdbItem->getWfStatus());
        } catch (\InvalidArgumentException $exception) {
            $workflowStatus = WorkflowStatus::READY_FOR_VALIDATION();
        }
        $this->workflowStatus = $workflowStatus;
    }

    /**
     * Publish the offer when it has workflowstatus draft.
     * @param \DateTimeInterface $publicationDate
     */
    public function publish(\DateTimeInterface $publicationDate)
    {
        $this->guardPublish() ?: $this->apply(
            $this->createPublishedEvent($publicationDate)
        );
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function guardPublish()
    {
        if ($this->workflowStatus === WorkflowStatus::READY_FOR_VALIDATION()) {
            return true; // nothing left to do if the offer has already been published
        }

        if ($this->workflowStatus !== WorkflowStatus::DRAFT()) {
            throw new Exception('You can not publish an offer that is not draft');
        }

        return false;
    }

    /**
     * Approve the offer when it's waiting for validation.
     */
    public function approve()
    {
        $this->guardApprove() ?: $this->apply($this->createApprovedEvent());
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function guardApprove()
    {
        if ($this->workflowStatus === WorkflowStatus::APPROVED()) {
            return true; // nothing left to do if the offer has already been approved
        }

        if ($this->workflowStatus !== WorkflowStatus::READY_FOR_VALIDATION()) {
            throw new Exception('You can not approve an offer that is not ready for validation');
        }

        return false;
    }

    /**
     * Reject an offer that is waiting for validation with a given reason.
     * @param StringLiteral $reason
     */
    public function reject(StringLiteral $reason)
    {
        $this->guardRejection($reason) ?: $this->apply($this->createRejectedEvent($reason));
    }

    public function flagAsDuplicate()
    {
        $reason = new StringLiteral(self::DUPLICATE_REASON);
        $this->guardRejection($reason) ?: $this->apply($this->createFlaggedAsDuplicate());
    }

    public function flagAsInappropriate()
    {
        $reason = new StringLiteral(self::INAPPROPRIATE_REASON);
        $this->guardRejection($reason) ?: $this->apply($this->createFlaggedAsInappropriate());
    }

    /**
     * @param StringLiteral $reason
     * @return bool
     *  false when the offer can still be rejected, true when the offer is already rejected for the same reason
     * @throws Exception
     */
    private function guardRejection(StringLiteral $reason)
    {
        if ($this->workflowStatus === WorkflowStatus::REJECTED()) {
            if ($this->rejectedReason && $reason->sameValueAs($this->rejectedReason)) {
                return true; // nothing left to do if the offer has already been rejected for the same reason
            } else {
                throw new Exception('The offer has already been rejected for another reason: ' . $this->rejectedReason);
            }
        }

        if ($this->workflowStatus !== WorkflowStatus::READY_FOR_VALIDATION()) {
            throw new Exception('You can not reject an offer that is not ready for validation');
        }

        return false;
    }

    /**
     * Overwrites or resets the main image and all media objects
     * by importing a new collection of images from UDB2.
     *
     * @param ImageCollection $images
     */
    public function importImagesFromUDB2(ImageCollection $images)
    {
        $this->apply($this->createImagesImportedFromUDB2($images));
    }

    /**
     * Overwrites or resets the main image and all media objects
     * by updating with a new collection of images from UDB2.
     *
     * @param ImageCollection $images
     */
    public function updateImagesFromUDB2(ImageCollection $images)
    {
        $this->apply($this->createImagesUpdatedFromUDB2($images));
    }

    /**
     * @param AbstractPublished $published
     */
    protected function applyPublished(AbstractPublished $published)
    {
        $this->workflowStatus = WorkflowStatus::READY_FOR_VALIDATION();
    }

    /**
     * @param AbstractApproved $approved
     */
    protected function applyApproved(AbstractApproved $approved)
    {
        $this->workflowStatus = WorkflowStatus::APPROVED();
    }

    /**
     * @param AbstractRejected $rejected
     */
    protected function applyRejected(AbstractRejected $rejected)
    {
        $this->rejectedReason = $rejected->getReason();
        $this->workflowStatus = WorkflowStatus::REJECTED();
    }

    /**
     * @param AbstractFlaggedAsDuplicate $flaggedAsDuplicate
     */
    protected function applyFlaggedAsDuplicate(AbstractFlaggedAsDuplicate $flaggedAsDuplicate)
    {
        $this->rejectedReason = new StringLiteral(self::DUPLICATE_REASON);
        $this->workflowStatus = WorkflowStatus::REJECTED();
    }

    /**
     * @param AbstractFlaggedAsInappropriate $flaggedAsInappropriate
     */
    protected function applyFlaggedAsInappropriate(AbstractFlaggedAsInappropriate $flaggedAsInappropriate)
    {
        $this->rejectedReason = new StringLiteral(self::INAPPROPRIATE_REASON);
        $this->workflowStatus = WorkflowStatus::REJECTED();
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

    protected function applyOrganizerUpdated(AbstractOrganizerUpdated $organizerUpdated)
    {
        $this->organizerId = $organizerUpdated->getOrganizerId();
    }

    protected function applyOrganizerDeleted(AbstractOrganizerDeleted $organizerDeleted)
    {
        $this->organizerId = null;
    }

    /**
     * @param AbstractImagesImportedFromUDB2 $imagesImportedFromUDB2
     */
    protected function applyImagesImportedFromUDB2(AbstractImagesImportedFromUDB2 $imagesImportedFromUDB2)
    {
        $this->applyImagesEvent($imagesImportedFromUDB2);
    }

    /**
     * @param AbstractImagesUpdatedFromUDB2 $imagesUpdatedFromUDB2
     */
    protected function applyImagesUpdatedFromUDB2(AbstractImagesUpdatedFromUDB2 $imagesUpdatedFromUDB2)
    {
        $this->applyImagesEvent($imagesUpdatedFromUDB2);
    }

    /**
     * Overwrites or resets the main image and all media objects.
     *
     * @param AbstractImagesEvent $imagesEvent
     */
    protected function applyImagesEvent(AbstractImagesEvent $imagesEvent)
    {
        $mainImage = $imagesEvent->getImages()->getMain();

        $this->mainImageId = $mainImage ? $mainImage->getMediaObjectId() : null;

        $this->mediaObjects = array_map(
            function (Image $image) {
                return $image->getMediaObjectId();
            },
            $imagesEvent->getImages()->toArray()
        );
    }

    /**
     * @param Label $label
     * @return AbstractLabelAdded
     */
    abstract protected function createLabelAddedEvent(Label $label);

    /**
     * @param Label $label
     * @return AbstractLabelRemoved
     */
    abstract protected function createLabelRemovedEvent(Label $label);

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

    /**
     * @param PriceInfo $priceInfo
     * @return AbstractPriceInfoUpdated
     */
    abstract protected function createPriceInfoUpdatedEvent(PriceInfo $priceInfo);

    /**
     * @param \DateTimeInterface $publicationDate
     * @return AbstractPublished
     */
    abstract protected function createPublishedEvent(\DateTimeInterface $publicationDate);

    /**
     * @return AbstractApproved
     */
    abstract protected function createApprovedEvent();

    /**
     * @param StringLiteral $reason
     * @return AbstractRejected
     */
    abstract protected function createRejectedEvent(StringLiteral $reason);

    /**
     * @return AbstractFlaggedAsDuplicate
     */
    abstract protected function createFlaggedAsDuplicate();

    /**
     * @return AbstractFlaggedAsInappropriate
     */
    abstract protected function createFlaggedAsInappropriate();

    /**
     * @param ImageCollection $images
     * @return AbstractImagesImportedFromUDB2
     */
    abstract protected function createImagesImportedFromUDB2(ImageCollection $images);

    /**
     * @param ImageCollection $images
     * @return AbstractImagesUpdatedFromUDB2
     */
    abstract protected function createImagesUpdatedFromUDB2(ImageCollection $images);
}
