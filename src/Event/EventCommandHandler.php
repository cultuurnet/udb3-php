<?php


namespace CultuurNet\UDB3\Event;

use Broadway\Repository\RepositoryInterface;

use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use CultuurNet\UDB3\Event\Commands\AddImage;
use CultuurNet\UDB3\Event\Commands\DeleteImage;
use CultuurNet\UDB3\Event\Commands\DeleteOrganizer;
use CultuurNet\UDB3\Event\Commands\UpdateBookingInfo;
use CultuurNet\UDB3\Event\Commands\UpdateContactPoint;
use CultuurNet\UDB3\Event\Commands\UpdateDescription;
use CultuurNet\UDB3\Event\Commands\UpdateImage;
use CultuurNet\UDB3\Event\Commands\UpdateMajorInfo;
use CultuurNet\UDB3\Event\Commands\UpdateOrganizer;
use CultuurNet\UDB3\Event\Commands\UpdateTypicalAgeRange;
use CultuurNet\UDB3\Keyword;
use CultuurNet\UDB3\Event\Commands\ApplyLabel;
use CultuurNet\UDB3\Event\Commands\LabelEvents;
use CultuurNet\UDB3\Event\Commands\LabelQuery;
use CultuurNet\UDB3\Event\Commands\Unlabel;
use CultuurNet\UDB3\Label as Label;

use CultuurNet\UDB3\Search\SearchServiceInterface;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class EventCommandHandler extends Udb3CommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var RepositoryInterface
     */
    protected $eventRepository;

    /**
     * @var SearchServiceInterface
     */
    protected $searchService;

    public function __construct(
        RepositoryInterface $eventRepository,
        SearchServiceInterface $searchService
    ) {
        $this->eventRepository = $eventRepository;
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
        $totalItemCount = 0;

        // do a pre query to test if the query is valid and check the item count
        try {
            $preQueryResult = $this->searchService->search($query, 1, 0);
            $totalItemCount = $preQueryResult['totalItems'];
        } catch (ClientErrorResponseException $e) {
            if ($this->logger) {
                $this->logger->error(
                    'query_was_not_labelled',
                    array(
                        'query' => $query,
                        'error' => $e->getMessage(),
                        'exception_class' => get_class($e),
                    )
                );
            }
        }

        if ($totalItemCount < 1) {
            if ($this->logger) {
                $this->logger->error(
                    'query_was_not_labelled',
                    array(
                        'query' => $query,
                        'error' => "query did not return any results"
                    )
                );
            }
        } else {
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
                foreach ($results['member'] as $event) {
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
        $event = $this->eventRepository->load($eventId);
        $event->label($label);
        try {
            $this->eventRepository->add($event);

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
        $event = $this->eventRepository->load($translateTitle->getId());

        $event->translateTitle(
            $translateTitle->getLanguage(),
            $translateTitle->getTitle()
        );

        $this->eventRepository->add($event);
    }

    public function handleTranslateDescription(
        TranslateDescription $translateDescription
    ) {

        /** @var Event $event */
        $event = $this->eventRepository->load($translateDescription->getId());

        $event->translateDescription(
            $translateDescription->getLanguage(),
            $translateDescription->getDescription()
        );

        $this->eventRepository->add($event);
    }

    /**
     * Handle an update command to update the main description.
     */
    public function handleUpdateDescription(UpdateDescription $updateDescription)
    {

        /** @var Event $event */
        $event = $this->eventRepository->load($updateDescription->getId());

        $event->updateDescription(
            $updateDescription->getDescription()
        );

        $this->eventRepository->add($event);

    }

    /**
     * Handle an update command to update the typical age range.
     */
    public function handleUpdateTypicalAgeRange(UpdateTypicalAgeRange $typicalAgeRange)
    {

        /** @var Event $event */
        $event = $this->eventRepository->load($typicalAgeRange->getId());

        $event->updateTypicalAgeRange(
            $typicalAgeRange->getTypicalAgeRange()
        );

        $this->eventRepository->add($event);

    }

    /**
     * Handle an update command to update organizer.
     */
    public function handleUpdateOrganizer(UpdateOrganizer $updateOrganizer)
    {

        /** @var Event $event */
        $event = $this->eventRepository->load($updateOrganizer->getId());

        $event->updateOrganizer(
            $updateOrganizer->getOrganizerId()
        );

        $this->eventRepository->add($event);

    }

    /**
     * Handle an update command to delete the organizer.
     */
    public function handleDeleteOrganizer(DeleteOrganizer $deleteOrganizer)
    {

        /** @var Event $event */
        $event = $this->eventRepository->load($deleteOrganizer->getId());

        $event->deleteOrganizer(
            $deleteOrganizer->getOrganizerId()
        );

        $this->eventRepository->add($event);

    }

    /**
     * Handle an update command to updated the contact point.
     */
    public function handleUpdateContactPoint(UpdateContactPoint $updateContactPoint)
    {

        /** @var Event $event */
        $event = $this->eventRepository->load($updateContactPoint->getId());

        $event->updateContactPoint(
            $updateContactPoint->getContactPoint()
        );

        $this->eventRepository->add($event);

    }

    /**
     * Handle an update command to updated the booking info.
     */
    public function handleUpdateBookingInfo(UpdateBookingInfo $updateBookingInfo)
    {

        /** @var Event $event */
        $event = $this->eventRepository->load($updateBookingInfo->getId());

        $event->updateBookingInfo(
            $updateBookingInfo->getBookingInfo()
        );

        $this->eventRepository->add($event);

    }

    /**
     * Handle an add image command.
     * @param AddImage $addImage
     */
    public function handleAddImage(AddImage $addImage)
    {

        /** @var Event $event */
        $event = $this->eventRepository->load($addImage->getId());

        $event->addImage(
            $addImage->getMediaObject()
        );

        $this->eventRepository->add($event);

    }

    /**
     * Handle an update image command.
     * @param UpdateImage $updateImage
     */
    public function handleUpdateImage(UpdateImage $updateImage)
    {

        /** @var Event $event */
        $event = $this->eventRepository->load($updateImage->getId());

        $event->updateImage(
            $updateImage->getIndexToUpdate(),
            $updateImage->getMediaObject()
        );

        $this->eventRepository->add($event);

    }

    /**
     * Handle a delete image command.
     * @param DeleteImage $deleteImage
     */
    public function handleDeleteImage(DeleteImage $deleteImage)
    {

        /** @var Event $event */
        $event = $this->eventRepository->load($deleteImage->getId());

        $event->deleteImage(
            $deleteImage->getIndexToDelete(),
            $deleteImage->getInternalId()
        );

        $this->eventRepository->add($event);

    }

    /**
     * Handle an update the major info command.
     */
    public function handleUpdateMajorInfo(UpdateMajorInfo $updateMajorInfo)
    {

        /** @var Event $event */
        $event = $this->eventRepository->load($updateMajorInfo->getId());

        $event->updateMajorInfo(
            $updateMajorInfo->getTitle(),
            $updateMajorInfo->getEventType(),
            $updateMajorInfo->getLocation(),
            $updateMajorInfo->getCalendar(),
            $updateMajorInfo->getTheme()
        );

        $this->eventRepository->add($event);

    }

    public function handleApplyLabel(ApplyLabel $label)
    {
        /** @var Event $event */
        $event = $this->eventRepository->load($label->getEventId());
        $event->label($label->getLabel());

        $this->eventRepository->add($event);
    }

    public function handleUnlabel(Unlabel $label)
    {
        /** @var Event $event */
        $event = $this->eventRepository->load($label->getEventId());
        $event->unlabel($label->getLabel());

        $this->eventRepository->add($event);
    }
}
