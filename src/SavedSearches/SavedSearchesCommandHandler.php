<?php

namespace CultuurNet\UDB3\SavedSearches;

use Broadway\CommandHandling\CommandHandler;
use CultuurNet\UDB3\CommandHandling\ContextAwareInterface;
use CultuurNet\UDB3\CommandHandling\ContextAwareTrait;
use CultuurNet\UDB3\SavedSearches\Command\SubscribeToSavedSearch;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class SavedSearchesCommandHandler extends CommandHandler implements LoggerAwareInterface, ContextAwareInterface
{
    use ContextAwareTrait;
    use LoggerAwareTrait;

    /**
     * @var SavedSearchesServiceFactoryInterface
     */
    protected $savedSearchesServiceFactory;

    /**
     * @param SavedSearchesServiceFactoryInterface $savedSearchesServiceFactory
     */
    public function __construct(SavedSearchesServiceFactoryInterface $savedSearchesServiceFactory)
    {
        $this->savedSearchesServiceFactory = $savedSearchesServiceFactory;
    }

    /**
     * @param SubscribeToSavedSearch $subscribeToSavedSearch
     */
    public function handleSubscribeToSavedSearch(SubscribeToSavedSearch $subscribeToSavedSearch)
    {
        $userId = (string) $subscribeToSavedSearch->getUserId();
        $name = (string) $subscribeToSavedSearch->getName();
        $query = $subscribeToSavedSearch->getQuery()->toURLQueryString();

        $metadata = $this->metadata->serialize();
        $tokenCredentials = $metadata['uitid_token_credentials'];

        $savedSearch = new SavedSearch($userId, $name, $query, SavedSearch::NEVER);
        $savedSearchesService = $this->savedSearchesServiceFactory->withTokenCredentials(
            $tokenCredentials
        );

        try {
            $savedSearchesService->subscribe($savedSearch);
        } catch (\Exception $exception) {
            if ($this->logger) {
                $this->logger->error(
                    'saved_search_was_not_subscribed',
                    [
                        'error' => $exception->getMessage(),
                        'userId' => $userId,
                        'name' => $name,
                        'query' => $subscribeToSavedSearch->getQuery(),
                        'frequency' => $savedSearch->frequency,
                    ]
                );
            }
        }
    }
}
