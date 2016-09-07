<?php

namespace CultuurNet\UDB3\Place;

use CultuurNet\UDB3\Offer\OfferCommandHandler;
use CultuurNet\UDB3\Place\Commands\AddImage;
use CultuurNet\UDB3\Place\Commands\Moderation\Approve;
use CultuurNet\UDB3\Place\Commands\RemoveImage;
use CultuurNet\UDB3\Place\Commands\AddLabel;
use CultuurNet\UDB3\Place\Commands\DeleteLabel;
use CultuurNet\UDB3\Place\Commands\DeleteOrganizer;
use CultuurNet\UDB3\Place\Commands\DeletePlace;
use CultuurNet\UDB3\Place\Commands\DeleteTypicalAgeRange;
use CultuurNet\UDB3\Place\Commands\SelectMainImage;
use CultuurNet\UDB3\Place\Commands\TranslateDescription;
use CultuurNet\UDB3\Place\Commands\TranslateTitle;
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

    /**
     * @return string
     */
    protected function getAddLabelClassName()
    {
        return AddLabel::class;
    }

    /**
     * @return string
     */
    protected function getDeleteLabelClassName()
    {
        return DeleteLabel::class;
    }

    /**
     * @return string
     */
    protected function getAddImageClassName()
    {
        return AddImage::class;
    }

    /**
     * @return string
     */
    protected function getUpdateImageClassName()
    {
        return UpdateImage::class;
    }

    /**
     * @return string
     */
    protected function getRemoveImageClassName()
    {
        return RemoveImage::class;
    }

    /**
     * @return string
     */
    protected function getSelectMainImageClassName()
    {
        return SelectMainImage::class;
    }

    /**
     * @return string
     */
    protected function getTranslateTitleClassName()
    {
        return TranslateTitle::class;
    }

    /**
     * @return string
     */
    protected function getTranslateDescriptionClassName()
    {
        return TranslateDescription::class;
    }

    /**
     * @return string
     */
    protected function getUpdateDescriptionClassName()
    {
        return UpdateDescription::class;
    }

    /**
     * @return string
     */
    protected function getUpdateTypicalAgeRangeClassName()
    {
        return UpdateTypicalAgeRange::class;
    }

    /**
     * @return string
     */
    protected function getDeleteTypicalAgeRangeClassName()
    {
        return DeleteTypicalAgeRange::class;
    }

    /**
     * @return string
     */
    protected function getUpdateOrganizerClassName()
    {
        return UpdateOrganizer::class;
    }

    /**
     * @return string
     */
    protected function getDeleteOrganizerClassName()
    {
        return DeleteOrganizer::class;
    }

    /**
     * @return string
     */
    protected function getUpdateContactPointClassName()
    {
        return UpdateContactPoint::class;
    }

    /**
     * @return string
     */
    protected function getUpdateBookingInfoClassName()
    {
        return UpdateBookingInfo::class;
    }

    /**
     * @return string
     */
    protected function getDeleteOfferClassName()
    {
        return DeletePlace::class;
    }

    protected function getApproveClassName()
    {
        return Approve::class;
    }

    /**
     * Handle the update of facilities for a place.
     */
    public function handleUpdateFacilities(UpdateFacilities $updateFacilities)
    {

        /** @var Place $place */
        $place = $this->offerRepository->load($updateFacilities->getItemId());

        $place->updateFacilities(
            $updateFacilities->getFacilities()
        );

        $this->offerRepository->save($place);
    }

    /**
     * Handle an update the major info command.
     */
    public function handleUpdateMajorInfo(UpdateMajorInfo $updateMajorInfo)
    {

        /** @var Place $place */
        $place = $this->offerRepository->load($updateMajorInfo->getItemId());

        $place->updateMajorInfo(
            $updateMajorInfo->getTitle(),
            $updateMajorInfo->getEventType(),
            $updateMajorInfo->getAddress(),
            $updateMajorInfo->getCalendar(),
            $updateMajorInfo->getTheme()
        );

        $this->offerRepository->save($place);

    }
}
