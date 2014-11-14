<?php


namespace CultuurNet\UDB3\Event;

use Broadway\CommandHandling\CommandHandler;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Keyword;
use CultuurNet\UDB3\Search\SearchServiceInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class EventCommandHandler extends CommandHandler implements LoggerAwareInterface
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
      SearchServiceInterface $searchService)
    {
        $this->eventRepository = $eventRepository;
        $this->searchService = $searchService;
    }

    public function handleTagEvents(TagEvents $tagEvents)
    {
        $this->tagEventsById($tagEvents->getEventIds(), $tagEvents->getKeyword());
    }

    public function handleTagQuery(TagQuery $tagQuery)
    {
        $query = $tagQuery->getQuery();
        $totalItemCount = 0;

        // do a pre query to test if the query is valid and check the item count
        try {
            $preQueryResult = $this->searchService->search($query, 0, 0);
            $totalItemCount = $preQueryResult['totalItems'];
        }
        catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
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

        if($totalItemCount < 1) {
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
            $allResults = $this->searchService->search($query, $totalItemCount, 0);
            $eventIds = [];

            foreach($allResults['member'] as $event) {
                $expoId = explode('/',$event['@id']);
                $eventIds[] = array_pop($expoId);
            }

            $this->tagEventsById($eventIds, $tagQuery->getKeyword());
        };
    }

    protected function tagEventsById ($eventIds, Keyword $keyword)
    {
        foreach ($eventIds as $eventId) {
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
    }


}
