<?php

namespace CultuurNet\UDB3\Offer\Item;

use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Offer\Commands\Image\AbstractUpdateImage;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Events\AbstractDescriptionTranslated;
use CultuurNet\UDB3\Offer\Events\AbstractTitleTranslated;
use CultuurNet\UDB3\Offer\Item\Events\BookingInfoUpdated;
use CultuurNet\UDB3\Offer\Item\Events\ContactPointUpdated;
use CultuurNet\UDB3\Offer\Item\Events\DescriptionTranslated;
use CultuurNet\UDB3\Offer\Item\Events\DescriptionUpdated;
use CultuurNet\UDB3\Offer\Item\Events\ItemCreated;
use CultuurNet\UDB3\Offer\Item\Events\ItemDeleted;
use CultuurNet\UDB3\Offer\Item\Events\LabelAdded;
use CultuurNet\UDB3\Offer\Item\Events\LabelDeleted;
use CultuurNet\UDB3\Offer\Item\Events\MainImageSelected;
use CultuurNet\UDB3\Offer\Item\Events\OrganizerDeleted;
use CultuurNet\UDB3\Offer\Item\Events\OrganizerUpdated;
use CultuurNet\UDB3\Offer\Item\Events\TitleTranslated;
use CultuurNet\UDB3\Offer\Item\Events\TypicalAgeRangeDeleted;
use CultuurNet\UDB3\Offer\Item\Events\TypicalAgeRangeUpdated;
use CultuurNet\UDB3\Offer\Offer;
use CultuurNet\UDB3\Offer\Item\Events\ImageAdded;
use CultuurNet\UDB3\Offer\Item\Events\ImageRemoved;
use CultuurNet\UDB3\Offer\Item\Events\ImageUpdated;
use ValueObjects\String\String as StringLiteral;

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

    protected function createMainImageSelectedEvent(Image $image)
    {
        return new MainImageSelected($this->id, $image);
    }

    /**
     * @return mixed
     */
    public function getAggregateRootId()
    {
        return $this->id;
    }

    /**
     * @param Language $language
     * @param StringLiteral $title
     * @return AbstractTitleTranslated
     */
    protected function createTitleTranslatedEvent(Language $language, StringLiteral $title)
    {
        return new TitleTranslated($this->id, $language, $title);
    }

    /**
     * @param Language $language
     * @param StringLiteral $description
     * @return AbstractDescriptionTranslated
     */
    protected function createDescriptionTranslatedEvent(Language $language, StringLiteral $description)
    {
        return new DescriptionTranslated($this->id, $language, $description);
    }

    /**
     * @param string $description
     * @return DescriptionUpdated
     */
    protected function createDescriptionUpdatedEvent($description)
    {
        return new DescriptionUpdated($this->id, $description);
    }

    /**
     * @param string $typicalAgeRange
     * @return TypicalAgeRangeUpdated
     */
    protected function createTypicalAgeRangeUpdatedEvent($typicalAgeRange)
    {
        return new TypicalAgeRangeUpdated($this->id, $typicalAgeRange);
    }

    /**
     * @return TypicalAgeRangeDeleted
     */
    protected function createTypicalAgeRangeDeletedEvent()
    {
        return new TypicalAgeRangeDeleted($this->id);
    }

    /**
     * @param string $organizerId
     * @return OrganizerUpdated
     */
    protected function createOrganizerUpdatedEvent($organizerId)
    {
        return new OrganizerUpdated($this->id, $organizerId);
    }

    /**
     * @param string $organizerId
     * @return OrganizerDeleted
     */
    protected function createOrganizerDeletedEvent($organizerId)
    {
        return new OrganizerDeleted($this->id, $organizerId);
    }

    /**
     * @param ContactPoint $contactPoint
     * @return ContactPointUpdated
     */
    protected function createContactPointUpdatedEvent(ContactPoint $contactPoint)
    {
        return new ContactPointUpdated($this->id, $contactPoint);
    }

    /**
     * @param BookingInfo $bookingInfo
     * @return BookingInfoUpdated
     */
    protected function createBookingInfoUpdatedEvent(BookingInfo $bookingInfo)
    {
        return new BookingInfoUpdated($this->id, $bookingInfo);
    }

    protected function createOfferDeletedEvent()
    {
        return new ItemDeleted();
    }
}
