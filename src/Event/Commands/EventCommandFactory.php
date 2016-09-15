<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Event\Commands\Moderation\Approve;
use CultuurNet\UDB3\Event\Commands\Moderation\FlagAsDuplicate;
use CultuurNet\UDB3\Event\Commands\Moderation\FlagAsInappropriate;
use CultuurNet\UDB3\Event\Commands\Moderation\Reject;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Offer\Commands\AbstractAddLabel;
use CultuurNet\UDB3\Offer\Commands\AbstractDeleteLabel;
use CultuurNet\UDB3\Offer\Commands\AbstractDeleteOffer;
use CultuurNet\UDB3\Offer\Commands\AbstractDeleteOrganizer;
use CultuurNet\UDB3\Offer\Commands\AbstractDeleteTypicalAgeRange;
use CultuurNet\UDB3\Offer\Commands\AbstractTranslateDescription;
use CultuurNet\UDB3\Offer\Commands\AbstractTranslateTitle;
use CultuurNet\UDB3\Offer\Commands\AbstractUpdateBookingInfo;
use CultuurNet\UDB3\Offer\Commands\AbstractUpdateContactPoint;
use CultuurNet\UDB3\Offer\Commands\AbstractUpdateDescription;
use CultuurNet\UDB3\Offer\Commands\AbstractUpdateOrganizer;
use CultuurNet\UDB3\Offer\Commands\AbstractUpdateTypicalAgeRange;
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

    /**
     * @param $id
     * @param Image $image
     * @return AddImage
     */
    public function createAddImageCommand($id, Image $image)
    {
        return new AddImage($id, $image);
    }

    /**
     * @param $id
     * @param Image $image
     * @return RemoveImage
     */
    public function createRemoveImageCommand($id, Image $image)
    {
        return new RemoveImage($id, $image);
    }

    /**
     * @param $id
     * @param UUID $mediaObjectId
     * @param StringLiteral $description
     * @param StringLiteral $copyrightHolder
     * @return UpdateImage
     */
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
     * @param Image $image
     * @return SelectMainImage
     */
    public function createSelectMainImageCommand($id, Image $image)
    {
        return new SelectMainImage($id, $image);
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

    /**
     * @param string $id
     * @param string $description
     * @return AbstractUpdateDescription
     */
    public function createUpdateDescriptionCommand($id, $description)
    {
        return new UpdateDescription($id, $description);
    }

    /**
     * @param string $id
     * @param string $ageRange
     * @return AbstractUpdateTypicalAgeRange
     */
    public function createUpdateTypicalAgeRangeCommand($id, $ageRange)
    {
        return new UpdateTypicalAgeRange($id, $ageRange);
    }

    /**
     * @param string $id
     * @return AbstractDeleteTypicalAgeRange
     */
    public function createDeleteTypicalAgeRangeCommand($id)
    {
        return new DeleteTypicalAgeRange($id);
    }

    /**
     * @param string $id
     * @param string $organizerId
     * @return AbstractUpdateOrganizer
     */
    public function createUpdateOrganizerCommand($id, $organizerId)
    {
        return new UpdateOrganizer($id, $organizerId);
    }

    /**
     * @param string $id
     * @param string $organizerId
     * @return AbstractDeleteOrganizer
     */
    public function createDeleteOrganizerCommand($id, $organizerId)
    {
        return new DeleteOrganizer($id, $organizerId);
    }

    /**
     * @param string $id
     * @param ContactPoint $contactPoint
     * @return AbstractUpdateContactPoint
     */
    public function createUpdateContactPointCommand($id, ContactPoint $contactPoint)
    {
        return new UpdateContactPoint($id, $contactPoint);
    }

    /**
     * @param string $id
     * @param BookingInfo $bookingInfo
     * @return AbstractUpdateBookingInfo
     */
    public function createUpdateBookingInfoCommand($id, BookingInfo $bookingInfo)
    {
        return new UpdateBookingInfo($id, $bookingInfo);
    }

    /**
     * @param string $id
     * @return AbstractDeleteOffer
     */
    public function createDeleteOfferCommand($id)
    {
        return new DeleteEvent($id);
    }

    /**
     * @param string $id
     * @return Approve
     */
    public function createApproveCommand($id)
    {
        return new Approve($id);
    }

    /**
     * @param string $id
     * @param StringLiteral $reason
     * @return Reject
     */
    public function createRejectCommand($id, StringLiteral $reason)
    {
        return new Reject($id, $reason);
    }

    /**
     * @param string $id
     * @return FlagAsInappropriate
     */
    public function createFlagAsInappropriate($id)
    {
        return new FlagAsInappropriate($id);
    }

    /**
     * @param string $id
     * @return FlagAsDuplicate
     */
    public function createFlagAsDuplicate($id)
    {
        return new FlagAsDuplicate($id);
    }
}
