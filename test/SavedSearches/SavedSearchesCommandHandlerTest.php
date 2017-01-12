<?php

namespace CultuurNet\UDB3\SavedSearches;

use Broadway\Domain\Metadata;
use \CultureFeed_SavedSearches as SavedSearches;
use \CultureFeed_SavedSearches_SavedSearch as SavedSearch;
use CultuurNet\Auth\TokenCredentials;
use CultuurNet\UDB3\SavedSearches\Command\SubscribeToSavedSearch;
use CultuurNet\UDB3\SavedSearches\Command\UnsubscribeFromSavedSearch;
use CultuurNet\UDB3\SavedSearches\Properties\QueryString;
use Psr\Log\LoggerInterface;
use ValueObjects\StringLiteral\StringLiteral;

class SavedSearchesCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SavedSearches|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $savedSearchesService;

    /**
     * @var SavedSearchesCommandHandler
     */
    protected $commandHandler;

    public function setUp()
    {
        $this->savedSearchesService = $this->createMock(
            SavedSearches::class
        );

        $this->commandHandler = $this->getSavedSearchesCommandHandlerWithService(
            $this->savedSearchesService
        );
    }

    /**
     * @test
     */
    public function it_can_handle_subscribe_to_saved_search_commands()
    {
        $subscribeToSavedSearch = $this->aSubscribeToSavedSearchCommand();

        // We expect the service to be called to subscribe to a saved search.
        $savedSearch = new SavedSearch(
            $subscribeToSavedSearch->getUserId(),
            $subscribeToSavedSearch->getName(),
            'q=city%3A%22Leuven%22',
            SavedSearch::NEVER
        );

        $this->savedSearchesService->expects($this->once())
            ->method('subscribe')
            ->with($savedSearch);

        // Handle the "subscribe to saved search" command.
        $this->commandHandler->handle($subscribeToSavedSearch);
    }

    /**
     * @return SubscribeToSavedSearch
     */
    private function aSubscribeToSavedSearchCommand()
    {
        $userId = new StringLiteral('some-user-id');
        $name = new StringLiteral('My very first saved search!');
        $query = new QueryString('city:"Leuven"');

        $subscribeToSavedSearch = new SubscribeToSavedSearch($userId, $name, $query);

        return $subscribeToSavedSearch;
    }

    /**
     * @test
     */
    public function it_logs_subscribe_errors_when_they_occur()
    {
        $error = 'An unknown error has occurred.';

        // Subscribe command.
        $subscribeToSavedSearch = $this->aSubscribeToSavedSearchCommand();

        // We expect subscribe method of the saved searches service to throw an exception.
        $this->savedSearchesService->expects($this->once())
            ->method('subscribe')
            ->willThrowException(new \CultureFeed_Exception($error, 'UNKNOWN_ERROR'));

        // We expect the logger's error method to be called when the exception is thrown.
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with(
                'saved_search_was_not_subscribed',
                [
                    'error' => $error,
                    'userId' => (string) $subscribeToSavedSearch->getUserId(),
                    'name' => (string) $subscribeToSavedSearch->getName(),
                    'query' => (string) $subscribeToSavedSearch->getQuery(),
                ]
            );
        $this->commandHandler->setLogger($logger);

        // Handle the "subscribe to saved search" command.
        // This will result in an exception being thrown, and logged.
        $this->commandHandler->handle($subscribeToSavedSearch);
    }

    /**
     * @test
     */
    public function it_can_handle_unsubscribe_from_saved_search_commands()
    {
        // Unsubscribe command and its values.
        $unsubscribeFromSavedSearch = $this->getUnsubscribeFromSavedSearchCommandStub();
        $userId = (string) $unsubscribeFromSavedSearch->getUserId();
        $searchId = (string) $unsubscribeFromSavedSearch->getSearchId();

        // We expect the unsubscribe method to be called on the saved searches services,
        // using the commands values as arguments.
        $this->savedSearchesService->expects($this->once())
            ->method('unsubscribe')
            ->with(
                $searchId,
                $userId
            );

        // Handle the unsubscribe command.
        $this->commandHandler->handle($unsubscribeFromSavedSearch);
    }

    /**
     * @test
     */
    public function it_logs_unsubscribe_errors_when_they_occur()
    {
        // Unsubscribe command.
        $unsubscribeFromSavedSearch = $this->getUnsubscribeFromSavedSearchCommandStub();

        // We expect the unsubscribe method of the saved searches service to throw an exception.
        $error = 'An unknown error has occurred.';
        $this->savedSearchesService->expects($this->once())
            ->method('unsubscribe')
            ->willThrowException(new \CultureFeed_Exception($error, 'UNKNOWN_ERROR'));

        // We expect the logger's error method to be called when the exception is thrown.
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with(
                'User was not unsubscribed from saved search.',
                [
                    'error' => $error,
                    'userId' => (string) $unsubscribeFromSavedSearch->getUserId(),
                    'searchId' => (string) $unsubscribeFromSavedSearch->getSearchId(),
                ]
            );
        $this->commandHandler->setLogger($logger);

        // Handle the unsubscribe command.
        // This will result in an exception being thrown, and logged.
        $this->commandHandler->handle($unsubscribeFromSavedSearch);
    }

    /**
     * @return UnsubscribeFromSavedSearch
     */
    private function getUnsubscribeFromSavedSearchCommandStub()
    {
        $userId = new StringLiteral('some-user-id');
        $searchId = new StringLiteral('some-search-id');

        return new UnsubscribeFromSavedSearch($userId, $searchId);
    }

    /**
     * @param SavedSearches $savedSearchesService
     * @return SavedSearchesCommandHandler
     */
    private function getSavedSearchesCommandHandlerWithService(SavedSearches $savedSearchesService)
    {
        $tokenCredentials = new TokenCredentials('token', 'secret');

        // Saved searches service factory used to instantiate the saved searches service.
        /** @var SavedSearchesServiceFactoryInterface|\PHPUnit_Framework_MockObject_MockObject $savedSearchesServiceFactory */
        $savedSearchesServiceFactory = $this->createMock(
            SavedSearchesServiceFactoryInterface::class
        );
        $savedSearchesServiceFactory->expects($this->once())
            ->method('withTokenCredentials')
            ->with($tokenCredentials)
            ->willReturn($savedSearchesService);

        $metadata = new Metadata([
            'uitid_token_credentials' => $tokenCredentials,
        ]);

        // Command handler with the factory object and context metadata.
        $commandHandler = new SavedSearchesCommandHandler($savedSearchesServiceFactory);
        $commandHandler->setContext($metadata);
        return $commandHandler;
    }
}
