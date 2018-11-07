<?php

namespace CultuurNet\UDB3\SavedSearches;

use Broadway\CommandHandling\CommandHandler;
use CultuurNet\UDB3\SavedSearches\Command\SubscribeToSavedSearch;
use CultuurNet\UDB3\SavedSearches\Command\UnsubscribeFromSavedSearch;

class UDB3SavedSearchesCommandHandler extends CommandHandler
{
    /**
     * @var SavedSearchRepositoryCollection
     */
    private $savedSearchRepositoryCollection;

    /**
     * @param SavedSearchRepositoryCollection $savedSearchRepositoryCollection
     */
    public function __construct(SavedSearchRepositoryCollection $savedSearchRepositoryCollection)
    {
        $this->savedSearchRepositoryCollection = $savedSearchRepositoryCollection;
    }

    /**
     * @param SubscribeToSavedSearch $subscribeToSavedSearch
     */
    public function handleSubscribeToSavedSearch(SubscribeToSavedSearch $subscribeToSavedSearch): void
    {
        $userId = $subscribeToSavedSearch->getUserId();
        $name = $subscribeToSavedSearch->getName();
        $query = $subscribeToSavedSearch->getQuery();

        $savedSearchRepository = $this->savedSearchRepositoryCollection->getRepository(
            $subscribeToSavedSearch->getSapiVersion()
        );

        $savedSearchRepository->write($userId, $name, $query);
    }

    /**
     * @param UnsubscribeFromSavedSearch $unsubscribeFromSavedSearch
     */
    public function handleUnsubscribeFromSavedSearch(UnsubscribeFromSavedSearch $unsubscribeFromSavedSearch): void
    {
        $userId = $unsubscribeFromSavedSearch->getUserId();
        $searchId = $unsubscribeFromSavedSearch->getSearchId();

        $savedSearchRepository = $this->savedSearchRepositoryCollection->getRepository(
            $unsubscribeFromSavedSearch->getSapiVersion()
        );

        $savedSearchRepository->delete($userId, $searchId);
    }
}
