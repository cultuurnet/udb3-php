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
        $userId = $subscribeToSavedSearch->getUserId();
        $name = $subscribeToSavedSearch->getName();
        $query = $subscribeToSavedSearch->getQuery();

        $metadata = $this->metadata->serialize();
        $tokenCredentials = $metadata['uitid_token_credentials'];

        $savedSearchesService = $this->savedSearchesServiceFactory->withTokenCredentials($tokenCredentials);

        $repository = new UiTIDSavedSearchRepository($savedSearchesService);

        if ($this->logger) {
            $repository->setLogger($this->logger);
        }

        $repository->write($userId, $name, $query);
    }
}
