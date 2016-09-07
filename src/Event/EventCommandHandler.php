<?php


namespace CultuurNet\UDB3\Event;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use CultuurNet\UDB3\Event\Commands\AddImage;
use CultuurNet\UDB3\Event\Commands\AddLabel;
use CultuurNet\UDB3\Event\Commands\DeleteEvent;
use CultuurNet\UDB3\Event\Commands\DeleteLabel;
use CultuurNet\UDB3\Event\Commands\Moderation\Approve;
use CultuurNet\UDB3\Event\Commands\RemoveImage;
use CultuurNet\UDB3\Event\Commands\DeleteOrganizer;
use CultuurNet\UDB3\Event\Commands\DeleteTypicalAgeRange;
use CultuurNet\UDB3\Event\Commands\LabelEvents;
use CultuurNet\UDB3\Event\Commands\LabelQuery;
use CultuurNet\UDB3\Event\Commands\SelectMainImage;
use CultuurNet\UDB3\Event\Commands\TranslateDescription;
use CultuurNet\UDB3\Event\Commands\TranslateTitle;
use CultuurNet\UDB3\Event\Commands\UpdateBookingInfo;
use CultuurNet\UDB3\Event\Commands\UpdateContactPoint;
use CultuurNet\UDB3\Event\Commands\UpdateDescription;
use CultuurNet\UDB3\Event\Commands\UpdateImage;
use CultuurNet\UDB3\Event\Commands\UpdateMajorInfo;
use CultuurNet\UDB3\Event\Commands\UpdateOrganizer;
use CultuurNet\UDB3\Event\Commands\UpdateTypicalAgeRange;
use CultuurNet\UDB3\Event\Events\MainImageSelected;
use CultuurNet\UDB3\Label as Label;
use CultuurNet\UDB3\Offer\OfferCommandHandler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Commandhandler for events
 */
class EventCommandHandler extends OfferCommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Handle an update the major info command.
     */
    public function handleUpdateMajorInfo(UpdateMajorInfo $updateMajorInfo)
    {

        /** @var Event $event */
        $event = $this->offerRepository->load($updateMajorInfo->getItemId());

        $event->updateMajorInfo(
            $updateMajorInfo->getTitle(),
            $updateMajorInfo->getEventType(),
            $updateMajorInfo->getLocation(),
            $updateMajorInfo->getCalendar(),
            $updateMajorInfo->getTheme()
        );

        $this->offerRepository->save($event);

    }

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
        return DeleteEvent::class;
    }

    protected function getApproveClassName()
    {
        return Approve::class;
    }
}
