<?php


namespace CultuurNet\UDB3\Event;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use CultuurNet\UDB3\Event\Commands\AddImage;
use CultuurNet\UDB3\Event\Commands\AddLabel;
use CultuurNet\UDB3\Event\Commands\DeleteEvent;
use CultuurNet\UDB3\Event\Commands\DeleteLabel;
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
use CultuurNet\UDB3\Search\SearchServiceInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Commandhandler for events
 */
class EventCommandHandler extends OfferCommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var SearchServiceInterface
     */
    protected $searchService;

    public function __construct(
        RepositoryInterface $eventRepository,
        SearchServiceInterface $searchService
    ) {
        parent::__construct($eventRepository);
        $this->searchService = $searchService;
    }

    /**
     * Handle an update command to update the main description.
     */
    public function handleUpdateDescription(UpdateDescription $updateDescription)
    {

        /** @var Event $event */
        $event = $this->repository->load($updateDescription->getId());

        $event->updateDescription(
            $updateDescription->getDescription()
        );

        $this->repository->save($event);

    }

    /**
     * Handle an update command to update the typical age range.
     */
    public function handleUpdateTypicalAgeRange(UpdateTypicalAgeRange $updateTypicalAgeRange)
    {

        /** @var Event $event */
        $event = $this->repository->load($updateTypicalAgeRange->getId());

        $event->updateTypicalAgeRange(
            $updateTypicalAgeRange->getTypicalAgeRange()
        );

        $this->repository->save($event);

    }

    /**
     * Handle the deletion of typical age range on an event.
     */
    public function handleDeleteTypicalAgeRange(DeleteTypicalAgeRange $deleteTypicalAgeRange)
    {

        /** @var Event $event */
        $event = $this->repository->load($deleteTypicalAgeRange->getId());

        $event->deleteTypicalAgeRange();

        $this->repository->save($event);

    }

    /**
     * Handle an update command to update organizer.
     */
    public function handleUpdateOrganizer(UpdateOrganizer $updateOrganizer)
    {

        /** @var Event $event */
        $event = $this->repository->load($updateOrganizer->getId());

        $event->updateOrganizer(
            $updateOrganizer->getOrganizerId()
        );

        $this->repository->save($event);

    }

    /**
     * Handle an update command to delete the organizer.
     */
    public function handleDeleteOrganizer(DeleteOrganizer $deleteOrganizer)
    {

        /** @var Event $event */
        $event = $this->repository->load($deleteOrganizer->getId());

        $event->deleteOrganizer(
            $deleteOrganizer->getOrganizerId()
        );

        $this->repository->save($event);

    }

    /**
     * Handle an update command to updated the contact point.
     */
    public function handleUpdateContactPoint(UpdateContactPoint $updateContactPoint)
    {

        /** @var Event $event */
        $event = $this->repository->load($updateContactPoint->getId());

        $event->updateContactPoint(
            $updateContactPoint->getContactPoint()
        );

        $this->repository->save($event);

    }

    /**
     * Handle an update command to updated the booking info.
     */
    public function handleUpdateBookingInfo(UpdateBookingInfo $updateBookingInfo)
    {

        /** @var Event $event */
        $event = $this->repository->load($updateBookingInfo->getId());

        $event->updateBookingInfo(
            $updateBookingInfo->getBookingInfo()
        );

        $this->repository->save($event);

    }

    /**
     * Handle an update the major info command.
     */
    public function handleUpdateMajorInfo(UpdateMajorInfo $updateMajorInfo)
    {

        /** @var Event $event */
        $event = $this->repository->load($updateMajorInfo->getId());

        $event->updateMajorInfo(
            $updateMajorInfo->getTitle(),
            $updateMajorInfo->getEventType(),
            $updateMajorInfo->getLocation(),
            $updateMajorInfo->getCalendar(),
            $updateMajorInfo->getTheme()
        );

        $this->repository->save($event);

    }

    /**
     * Handle a delete event command.
     */
    public function handleDeleteEvent(DeleteEvent $deleteEvent)
    {

        /** @var Event $event */
        $event = $this->repository->load($deleteEvent->getId());
        $event->deleteEvent();

        $this->repository->save($event);

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
}
