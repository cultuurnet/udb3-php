<?php

namespace CultuurNet\UDB3\SavedSearches;

use Broadway\CommandHandling\CommandHandler;
use CultuurNet\UDB3\SavedSearches\Command\SubscribeToSavedSearch;
use CultuurNet\UDB3\SavedSearches\Command\UnsubscribeFromSavedSearch;
use CultuurNet\UDB3\SavedSearches\WriteModel\SavedSearchRepositoryInterface;

class UDB3SavedSearchesCommandHandler extends CommandHandler
{
    /**
     * @var SavedSearchRepositoryInterface
     */
    private $savedSearchRepository;

    /**
     * @param SavedSearchRepositoryInterface $savedSearchRepository
     */
    public function __construct(SavedSearchRepositoryInterface $savedSearchRepository)
    {
        $this->savedSearchRepository = $savedSearchRepository;
    }

    /**
     * @param SubscribeToSavedSearch $subscribeToSavedSearch
     */
    public function handleSubscribeToSavedSearch(SubscribeToSavedSearch $subscribeToSavedSearch)
    {
        $userId = $subscribeToSavedSearch->getUserId();
        $name = $subscribeToSavedSearch->getName();
        $query = $subscribeToSavedSearch->getQuery();


        $this->savedSearchRepository->write($userId, $name, $query);
    }

    /**
     * @param UnsubscribeFromSavedSearch $unsubscribeFromSavedSearch
     */
    public function handleUnsubscribeFromSavedSearch(UnsubscribeFromSavedSearch $unsubscribeFromSavedSearch)
    {
        $userId = $unsubscribeFromSavedSearch->getUserId();
        $searchId = $unsubscribeFromSavedSearch->getSearchId();

        $this->savedSearchRepository->delete($userId, $searchId);
    }
}
