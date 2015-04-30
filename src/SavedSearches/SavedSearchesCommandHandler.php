<?php

namespace CultuurNet\UDB3\SavedSearches;

use Broadway\CommandHandling\CommandHandler;
use CultuurNet\UDB3\CommandHandling\ContextAwareInterface;
use CultuurNet\UDB3\CommandHandling\ContextAwareTrait;
use CultuurNet\UDB3\SavedSearches\Command\SubscribeToSavedSearch;
use CultuurNet\UDB3\SavedSearches\Command\UnsubscribeFromSavedSearch;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class SavedSearchesCommandHandler
 * @package CultuurNet\UDB3\SavedSearches
 *
 * @property UiTIDSavedSearchRepository $repository
 */
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
        $userId = $subscribeToSavedSearch->getUserId();
        $name = $subscribeToSavedSearch->getName();
        $query = $subscribeToSavedSearch->getQuery();

        $this->repository->write($userId, $name, $query);
    }

    /**
     * @param UnsubscribeFromSavedSearch $unsubscribeFromSavedSearch
     */
    public function handleUnsubscribeFromSavedSearch(UnsubscribeFromSavedSearch $unsubscribeFromSavedSearch)
    {
        $userId = $unsubscribeFromSavedSearch->getUserId();
        $searchId = $unsubscribeFromSavedSearch->getSearchId();

        $this->repository->delete($userId, $searchId);
    }

    /**
     * Should be called inside the handle methods and not inside the constructor,
     * because the metadata property is set or overwritten right before a handle
     * method is called.
     *
     * @return UiTIDSavedSearchRepository
     */
    private function getUiTIDSavedSearchRepository()
    {
        $metadata = $this->metadata->serialize();
        $tokenCredentials = $metadata['uitid_token_credentials'];

        $savedSearchesService = $this->savedSearchesServiceFactory->withTokenCredentials($tokenCredentials);

        $repository = new UiTIDSavedSearchRepository($savedSearchesService);

        if ($this->logger) {
            $repository->setLogger($this->logger);
        }

        return $repository;
    }

    /**
     * @param $property
     * @return mixed
     */
    public function __get($property)
    {
        switch ($property) {
            case 'repository':
                return $this->getUiTIDSavedSearchRepository();
                break;
        }
    }
}
