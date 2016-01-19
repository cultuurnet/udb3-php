<?php

namespace CultuurNet\UDB3\Place;

use CultuurNet\UDB3\Offer\OfferCommandHandler;
use CultuurNet\UDB3\Place\Commands\AddImage;
use CultuurNet\UDB3\Place\Commands\AddLabel;
use CultuurNet\UDB3\Place\Commands\DeleteImage;
use CultuurNet\UDB3\Place\Commands\DeleteLabel;
use CultuurNet\UDB3\Place\Commands\DeleteOrganizer;
use CultuurNet\UDB3\Place\Commands\DeletePlace;
use CultuurNet\UDB3\Place\Commands\DeleteTypicalAgeRange;
use CultuurNet\UDB3\Place\Commands\UpdateBookingInfo;
use CultuurNet\UDB3\Place\Commands\UpdateContactPoint;
use CultuurNet\UDB3\Place\Commands\UpdateDescription;
use CultuurNet\UDB3\Place\Commands\UpdateFacilities;
use CultuurNet\UDB3\Place\Commands\UpdateImage;
use CultuurNet\UDB3\Place\Commands\UpdateMajorInfo;
use CultuurNet\UDB3\Place\Commands\UpdateOrganizer;
use CultuurNet\UDB3\Place\Commands\UpdateTypicalAgeRange;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Commandhandler for places.
 */
class CommandHandler extends OfferCommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected function getAddLabelClassName()
    {
        return AddLabel::class;
    }

    protected function getDeleteLabelClassName()
    {
        return DeleteLabel::class;
    }

    /**
     * Handle the update of description on a place.
     */
    public function handleUpdateDescription(UpdateDescription $updateDescription)
    {

        /** @var Place $place */
        $place = $this->repository->load($updateDescription->getId());

        $place->updateDescription(
            $updateDescription->getDescription()
        );

        $this->repository->save($place);

    }

    /**
     * Handle the update of typical age range on a place.
     */
    public function handleUpdateTypicalAgeRange(UpdateTypicalAgeRange $updateTypicalAgeRange)
    {

        /** @var Place $place */
        $place = $this->repository->load($updateTypicalAgeRange->getId());

        $place->updateTypicalAgeRange(
            $updateTypicalAgeRange->getTypicalAgeRange()
        );

        $this->repository->save($place);

    }

    /**
     * Handle the deletion of typical age range on a place.
     */
    public function handleDeleteTypicalAgeRange(DeleteTypicalAgeRange $deleteTypicalAgeRange)
    {

        /** @var Place $place */
        $place = $this->repository->load($deleteTypicalAgeRange->getId());

        $place->deleteTypicalAgeRange();

        $this->repository->save($place);

    }

    /**
     * Handle an update command to update organizer of a place.
     */
    public function handleUpdateOrganizer(UpdateOrganizer $updateOrganizer)
    {
        /** @var Place $place */
        $place = $this->repository->load($updateOrganizer->getId());

        $place->updateOrganizer(
            $updateOrganizer->getOrganizerId()
        );

        $this->repository->save($place);

    }

    /**
     * Handle an update command to delete the organizer.
     */
    public function handleDeleteOrganizer(DeleteOrganizer $deleteOrganizer)
    {

        /** @var Place $place */
        $place = $this->repository->load($deleteOrganizer->getId());

        $place->deleteOrganizer(
            $deleteOrganizer->getOrganizerId()
        );

        $this->repository->save($place);

    }

    /**
     * Handle an update command to updated the contact point.
     */
    public function handleUpdateContactPoint(UpdateContactPoint $updateContactPoint)
    {

        /** @var Place $place */
        $place = $this->repository->load($updateContactPoint->getId());

        $place->updateContactPoint(
            $updateContactPoint->getContactPoint()
        );

        $this->repository->save($place);

    }

    /**
     * Handle the update of facilities for a place.
     */
    public function handleUpdateFacilities(UpdateFacilities $updateFacilities)
    {

        /** @var Place $place */
        $place = $this->repository->load($updateFacilities->getId());

        $place->updateFacilities(
            $updateFacilities->getFacilities()
        );

        $this->repository->save($place);
    }

    /**
     * Handle an update command to updated the booking info.
     */
    public function handleUpdateBookingInfo(UpdateBookingInfo $updateBookingInfo)
    {

        /** @var Place $place */
        $place = $this->repository->load($updateBookingInfo->getId());

        $place->updateBookingInfo(
            $updateBookingInfo->getBookingInfo()
        );

        $this->repository->save($place);

    }


    /**
     * Handle an add image command.
     * @param AddImage $addImage
     */
    public function handleAddImage(AddImage $addImage)
    {

        /** @var Place $place */
        $place = $this->repository->load($addImage->getId());

        $place->addImage(
            $addImage->getMediaObject()
        );

        $this->repository->save($place);

    }

    /**
     * Handle an update image command.
     * @param UpdateImage $updateImage
     */
    public function handleUpdateImage(UpdateImage $updateImage)
    {

        /** @var Place $place */
        $place = $this->repository->load($updateImage->getId());

        $place->updateImage(
            $updateImage->getIndexToUpdate(),
            $updateImage->getMediaObject()
        );

        $this->repository->save($place);

    }

    /**
     * Handle a delete image command.
     * @param DeleteImage $deleteImage
     */
    public function handleDeleteImage(DeleteImage $deleteImage)
    {

        /** @var Place $place */
        $place = $this->repository->load($deleteImage->getId());

        $place->deleteImage(
            $deleteImage->getIndexToDelete(),
            $deleteImage->getInternalId()
        );

        $this->repository->save($place);

    }

    /**
     * Handle an update the major info command.
     */
    public function handleUpdateMajorInfo(UpdateMajorInfo $updateMajorInfo)
    {

        /** @var Place $place */
        $place = $this->repository->load($updateMajorInfo->getId());

        $place->updateMajorInfo(
            $updateMajorInfo->getTitle(),
            $updateMajorInfo->getEventType(),
            $updateMajorInfo->getAddress(),
            $updateMajorInfo->getCalendar(),
            $updateMajorInfo->getTheme()
        );

        $this->repository->save($place);

    }

    /**
     * Handle a delete place command.
     */
    public function handleDeletePlace(DeletePlace $deletePlace)
    {

        /** @var Place $place */
        $place = $this->repository->load($deletePlace->getId());
        $place->deletePlace();

        $this->repository->save($place);

    }
}
