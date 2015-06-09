<?php

namespace CultuurNet\UDB3\Place;

use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use CultuurNet\UDB3\Place\Commands\AddImage;
use CultuurNet\UDB3\Place\Commands\DeleteImage;
use CultuurNet\UDB3\Place\Commands\DeleteOrganizer;
use CultuurNet\UDB3\Place\Commands\DeletePlace;
use CultuurNet\UDB3\Place\Commands\UpdateBookingInfo;
use CultuurNet\UDB3\Place\Commands\UpdateContactPoint;
use CultuurNet\UDB3\Place\Commands\UpdateDescription;
use CultuurNet\UDB3\Place\Commands\UpdateFacilities;
use CultuurNet\UDB3\Place\Commands\UpdateImage;
use CultuurNet\UDB3\Place\Commands\UpdateMajorInfo;
use CultuurNet\UDB3\Place\Commands\UpdateOrganizer;
use CultuurNet\UDB3\Place\Commands\UpdateTypicalAgeRange;
use CultuurNet\UDB3\Place\Place;
use Broadway\Repository\RepositoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Commandhandler for places.
 */
class CommandHandler extends Udb3CommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var RepositoryInterface
     */
    protected $placeRepository;

    public function __construct(
        RepositoryInterface $placeRepository
    ) {
        $this->placeRepository = $placeRepository;
    }

    /**
     * Handle the update of description on a place.
     */
    public function handleUpdateDescription(UpdateDescription $updateDescription)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($updateDescription->getId());

        $place->updateDescription(
            $updateDescription->getDescription()
        );

        $this->placeRepository->save($place);

    }

    /**
     * Handle the update of typical age range on a place.
     */
    public function handleUpdateTypicalAgeRange(UpdateTypicalAgeRange $typicalAgeRange)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($typicalAgeRange->getId());

        $place->updateTypicalAgeRange(
            $typicalAgeRange->getTypicalAgeRange()
        );

        $this->placeRepository->save($place);

    }

    /**
     * Handle an update command to update organizer.
     */
    public function handleUpdateOrganizer(UpdateOrganizer $updateOrganizer)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($updateOrganizer->getId());

        $place->updateOrganizer(
            $updateOrganizer->getOrganizerId()
        );

        $this->placeRepository->save($place);

    }

    /**
     * Handle an update command to delete the organizer.
     */
    public function handleDeleteOrganizer(DeleteOrganizer $deleteOrganizer)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($deleteOrganizer->getId());

        $place->deleteOrganizer(
            $deleteOrganizer->getOrganizerId()
        );

        $this->placeRepository->save($place);

    }

    /**
     * Handle an update command to updated the contact point.
     */
    public function handleUpdateContactPoint(UpdateContactPoint $updateContactPoint)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($updateContactPoint->getId());

        $place->updateContactPoint(
            $updateContactPoint->getContactPoint()
        );

        $this->placeRepository->save($place);

    }

    /**
     * Handle the update of facilities for a place.
     */
    public function handleUpdateFacilities(UpdateFacilities $updateFacilities)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($updateFacilities->getId());

        $place->updateFacilities(
            $updateFacilities->getFacilities()
        );

        $this->placeRepository->save($place);
    }

    /**
     * Handle an update command to updated the booking info.
     */
    public function handleUpdateBookingInfo(UpdateBookingInfo $updateBookingInfo)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($updateBookingInfo->getId());

        $place->updateBookingInfo(
            $updateBookingInfo->getBookingInfo()
        );

        $this->placeRepository->save($place);

    }


    /**
     * Handle an add image command.
     * @param AddImage $addImage
     */
    public function handleAddImage(AddImage $addImage)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($addImage->getId());

        $place->addImage(
            $addImage->getMediaObject()
        );

        $this->placeRepository->save($place);

    }

    /**
     * Handle an update image command.
     * @param UpdateImage $updateImage
     */
    public function handleUpdateImage(UpdateImage $updateImage)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($updateImage->getId());

        $place->updateImage(
            $updateImage->getIndexToUpdate(),
            $updateImage->getMediaObject()
        );

        $this->placeRepository->save($place);

    }

    /**
     * Handle a delete image command.
     * @param DeleteImage $deleteImage
     */
    public function handleDeleteImage(DeleteImage $deleteImage)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($deleteImage->getId());

        $place->deleteImage(
            $deleteImage->getIndexToDelete(),
            $deleteImage->getInternalId()
        );

        $this->placeRepository->save($place);

    }

    /**
     * Handle an update the major info command.
     */
    public function handleUpdateMajorInfo(UpdateMajorInfo $updateMajorInfo)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($updateMajorInfo->getId());

        $place->updateMajorInfo(
            $updateMajorInfo->getTitle(),
            $updateMajorInfo->getEventType(),
            $updateMajorInfo->getAddress(),
            $updateMajorInfo->getCalendar(),
            $updateMajorInfo->getTheme()
        );

        $this->placeRepository->save($place);

    }

    /**
     * Handle a delete place command.
     */
    public function handleDeletePlace(DeletePlace $deletePlace)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($deletePlace->getId());
        $place->deletePlace();

        $this->placeRepository->save($place);

    }
}
