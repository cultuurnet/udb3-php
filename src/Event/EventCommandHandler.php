<?php


namespace CultuurNet\UDB3\Event;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Keyword;
use CultuurNet\UDB3\Search\SearchServiceInterface;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
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

    public function handleTagEvents(TagEvents $tagEvents)
    {
        foreach ($tagEvents->getEventIds() as $eventId) {
            $this->tagEvent($tagEvents->getKeyword(), $eventId);
        }
    }

    public function handleTagQuery(TagQuery $tagQuery)
    {
        $query = $tagQuery->getQuery();
        $totalItemCount = 0;

        // do a pre query to test if the query is valid and check the item count
        try {
            $preQueryResult = $this->searchService->search($query, 1, 0);
            $totalItemCount = $preQueryResult['totalItems'];
        } catch (ClientErrorResponseException $e) {
            if ($this->logger) {
                $this->logger->error(
                    'query_was_not_tagged',
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
                    'query_was_not_tagged',
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
            $taggedEventIds = [];

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

                    if (!array_key_exists($eventId, $taggedEventIds)) {
                        $taggedEventIds[$eventId] = $pageCounter;

                        $this->tagEvent($tagQuery->getKeyword(), $eventId);
                    } else {
                        if ($this->logger) {
                            $this->logger->error(
                                'query_duplicate_event',
                                array(
                                    'query' => $query,
                                    'error' => "found duplicate event {$eventId} on page {$pageCounter}, occurred first time on page {$taggedEventIds[$eventId]}"
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
     * Tags a single event with a keyword.
     *
     * @param Keyword $keyword
     * @param $eventId
     */
    private function tagEvent(Keyword $keyword, $eventId)
    {
        /** @var Event $event */
        $event = $this->eventRepository->load($eventId);
        $event->tag($keyword);
        try {
            $this->eventRepository->add($event);

            if ($this->logger) {
                $this->logger->info(
                    'event_was_tagged',
                    array(
                        'event_id' => $eventId,
                    )
                );
            }
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error(
                    'event_was_not_tagged',
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

    public function handleUpdateDescription(UpdateDescription $updateDescription)
    {

        /** @var Event $event */
        $event = $this->eventRepository->load($updateDescription->getId());

        $event->updateDescription(
            $updateDescription->getDescription()
        );

        $this->eventRepository->add($event);

    }

    public function handleUpdateTypicalAgeRange(UpdateTypicalAgeRange $typicalAgeRange)
    {

        /** @var Event $event */
        $event = $this->eventRepository->load($typicalAgeRange->getId());

        $event->updateTypicalAgeRange(
            $typicalAgeRange->getTypicalAgeRange()
        );

        $this->eventRepository->add($event);

    }

    public function handleTag(Tag $tag)
    {
        /** @var Event $event */
        $event = $this->eventRepository->load($tag->getEventId());
        $event->tag($tag->getKeyword());

        $this->eventRepository->add($event);
    }

    public function handleEraseTag(EraseTag $tag)
    {
        /** @var Event $event */
        $event = $this->eventRepository->load($tag->getEventId());
        $event->eraseTag($tag->getKeyword());

        $this->eventRepository->add($event);
    }
}
