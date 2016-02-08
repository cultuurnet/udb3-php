<?php


namespace CultuurNet\UDB3\Event;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use CultuurNet\UDB3\Event\Commands\AddImage;
use CultuurNet\UDB3\Event\Commands\AddLabel;
use CultuurNet\UDB3\Event\Commands\DeleteEvent;
use CultuurNet\UDB3\Event\Commands\RemoveImage;
use CultuurNet\UDB3\Event\Commands\DeleteOrganizer;
use CultuurNet\UDB3\Event\Commands\DeleteTypicalAgeRange;
use CultuurNet\UDB3\Event\Commands\LabelEvents;
use CultuurNet\UDB3\Event\Commands\LabelQuery;
use CultuurNet\UDB3\Event\Commands\DeleteLabel;
use CultuurNet\UDB3\Event\Commands\UpdateBookingInfo;
use CultuurNet\UDB3\Event\Commands\UpdateContactPoint;
use CultuurNet\UDB3\Event\Commands\UpdateDescription;
use CultuurNet\UDB3\Event\Commands\UpdateImage;
use CultuurNet\UDB3\Event\Commands\UpdateMajorInfo;
use CultuurNet\UDB3\Event\Commands\UpdateOrganizer;
use CultuurNet\UDB3\Event\Commands\UpdateTypicalAgeRange;
use CultuurNet\UDB3\Label as Label;
use CultuurNet\UDB3\Offer\OfferCommandHandler;
use CultuurNet\UDB3\Search\Results;
use CultuurNet\UDB3\Search\SearchServiceInterface;
use Guzzle\Http\Exception\ClientErrorResponseException;
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

    public function handleLabelEvents(LabelEvents $labelEvents)
    {
        foreach ($labelEvents->getEventIds() as $eventId) {
            $this->labelEvent($labelEvents->getLabel(), $eventId);
        }
    }

    public function handleLabelQuery(LabelQuery $labelQuery)
    {
        $query = $labelQuery->getQuery();

        // do a pre query to test if the query is valid and check the item count
        $preQueryResult = $this->searchService->search($query, 1, 0);
        $totalItemCount = $preQueryResult->getTotalItems()->toNative();

        if (0 === $totalItemCount) {
            return;
        }

        // change this pageSize value to increase or decrease the page size;
        $pageSize = 10;
        $pageCount = ceil($totalItemCount / $pageSize);
        $pageCounter = 0;
        $labelledEventIds = [];

        //Page querying the search service;
        while ($pageCounter < $pageCount) {
            $start = $pageCounter * $pageSize;
            // Sort ascending by creation date to make sure we get a quite consistent paging.
            $sort = 'creationdate asc';
            $results = $this->searchService->search(
                $query,
                $pageSize,
                $start,
                $sort
            );

            // Iterate the results of the current page and get their IDs
            // by stripping them from the json-LD representation
            foreach ($results->getItems() as $event) {
                $expoId = explode('/', $event['@id']);
                $eventId = array_pop($expoId);

                if (!array_key_exists($eventId, $labelledEventIds)) {
                    $labelledEventIds[$eventId] = $pageCounter;

                    $this->labelEvent($labelQuery->getLabel(), $eventId);
                } else {
                    if ($this->logger) {
                        $this->logger->error(
                            'query_duplicate_event',
                            array(
                                'query' => $query,
                                'error' => "found duplicate event {$eventId} on page {$pageCounter}, occurred first time on page {$labelledEventIds[$eventId]}"
                            )
                        );
                    }
                }
            }
            ++$pageCounter;
        };
    }

    /**
     * Labels a single event with a keyword.
     *
     * @param Label $label
     * @param $eventId
     */
    private function labelEvent(Label $label, $eventId)
    {
        /** @var Event $event */
        $event = $this->repository->load($eventId);
        $event->addLabel($label);
        try {
            $this->repository->save($event);

            if ($this->logger) {
                $this->logger->info(
                    'event_was_labelled',
                    array(
                        'event_id' => $eventId,
                    )
                );
            }
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error(
                    'event_was_not_labelled',
                    array(
                        'event_id' => $eventId,
                        'error' => $e->getMessage(),
                        'exception_class' => get_class($e),
                    )
                );
            }
        }
    }

    public function handleTranslateTitle(TranslateTitle $translateTitle)
    {
        /** @var Event $event */
        $event = $this->repository->load($translateTitle->getId());

        $event->translateTitle(
            $translateTitle->getLanguage(),
            $translateTitle->getTitle()
        );

        $this->repository->save($event);
    }

    public function handleTranslateDescription(
        TranslateDescription $translateDescription
    ) {

        /** @var Event $event */
        $event = $this->repository->load($translateDescription->getId());

        $event->translateDescription(
            $translateDescription->getLanguage(),
            $translateDescription->getDescription()
        );

        $this->repository->save($event);
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
     * Handle an add image command.
     * @param AddImage $addImage
     */
    public function handleAddImage(AddImage $addImage)
    {

        /** @var Event $event */
        $event = $this->repository->load($addImage->getId());

        $event->addImage(
            $addImage->getImage()
        );

        $this->repository->save($event);

    }

    /**
     * Handle an update image command.
     * @param UpdateImage $updateImage
     */
    public function handleUpdateImage(UpdateImage $updateImage)
    {
        /** @var Event $event */
        $event = $this->repository->load($updateImage->getItemId());
        $event->updateImage($updateImage);

        $this->repository->save($event);
    }

    /**
     * Handle a remove image command.
     * @param RemoveImage $removeImage
     */
    public function handleRemoveImage(RemoveImage $removeImage)
    {
        /** @var Event $event */
        $event = $this->repository->load($removeImage->getItemId());

        $event->removeImage($removeImage->getImage());

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
}
