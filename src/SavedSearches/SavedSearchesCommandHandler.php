<?php

namespace CultuurNet\UDB3\SavedSearches;

use \CultureFeed_SavedSearches as SavedSearches;
use \CultureFeed_SavedSearches_SavedSearch as SavedSearch;
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
     * @var SavedSearchesServiceFactory
     */
    protected $savedSearchesServiceFactory;

    /**
     * @param SavedSearches $savedSearchesService
     */
    public function __construct(SavedSearchesServiceFactory $savedSearchesServiceFactory)
    {
        $this->savedSearchesServiceFactory = $savedSearchesServiceFactory;
    }

    /**
     * @param SubscribeToSavedSearch $subscribeToSavedSearch
     */
    public function handleSubscribeToSavedSearch(SubscribeToSavedSearch $subscribeToSavedSearch)
    {
        $userId = $subscribeToSavedSearch->getUserId();
        $name = $subscribeToSavedSearch->getName();
        $query = $subscribeToSavedSearch->getQuery();
        $frequency = $subscribeToSavedSearch->getFrequency();

        $metadata = $this->metadata->serialize();
        $tokenCredentials = $metadata['uitid_token_credentials'];

        $savedSearch = new SavedSearch($userId, $name, $query, $frequency);
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
                        'query' => $query,
                        'frequency' => $frequency,
                    ]
                );
            }
        }
    }
}
