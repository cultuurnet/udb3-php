<?php

namespace CultuurNet\UDB3\SavedSearches;

use Broadway\Domain\Metadata;
use \CultureFeed_SavedSearches_Default as SavedSearches;
use \CultureFeed_SavedSearches_SavedSearch as SavedSearch;
use CultuurNet\Auth\TokenCredentials;
use CultuurNet\UDB3\SavedSearches\Command\SubscribeToSavedSearch;
use Guzzle\Log\ArrayLogAdapter;
use Psr\Log\LoggerInterface;

class SavedSearchesCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_handle_subscribe_to_saved_search_commands()
    {
        $userId = 'some-user-id';
        $name = 'My very first saved search!';
        $query = 'city:"Leuven"';
        $frequency = SavedSearch::NEVER;

        // Subscribe command.
        $subscribeToSavedSearch = new SubscribeToSavedSearch($userId, $name, $query);

        // We expect the service to be called to subscribe to a saved search.
        $savedSearch = new SavedSearch($userId, $name, $query, $frequency);
        $savedSearchesService = $this->getSavedSearchesService();
        $savedSearchesService->expects($this->once())
            ->method('subscribe')
            ->with($savedSearch);

        // Create the command handler.
        $commandHandler = $this->getSavedSearchesCommandHandlerWithService($savedSearchesService);

        // Handle the "subscribe to saved search" command.
        $commandHandler->handle($subscribeToSavedSearch);
    }


    /**
     * @test
     */
    public function it_logs_subscribe_errors_when_they_occur()
    {
        $error = 'An unknown error has occurred.';

        $userId = 'some-user-id';
        $name = 'My very first saved search!';
        $query = 'city:"Leuven"';
        $frequency = SavedSearch::NEVER;

        // Subscribe command.
        $subscribeToSavedSearch = new SubscribeToSavedSearch($userId, $name, $query);

        // We expect subscribe method of the saved searches service to throw an exception.
        $savedSearchesService = $this->getSavedSearchesService();
        $savedSearchesService->expects($this->once())
            ->method('subscribe')
            ->willThrowException(new \CultureFeed_Exception($error, 'UNKNOWN_ERROR'));

        // Create the command handler.
        $commandHandler = $this->getSavedSearchesCommandHandlerWithService($savedSearchesService);

        // We expect the logger's error method to be called when the exception is thrown.
        $logger = $this->getMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with(
                'saved_search_was_not_subscribed',
                [
                    'error' => $error,
                    'userId' => $userId,
                    'name' => $name,
                    'query' => $query,
                    'frequency' => $frequency,
                ]
            );
        $commandHandler->setLogger($logger);

        // Handle the "subscribe to saved search" command.
        // This will result in an exception being thrown, and logged.
        $commandHandler->handle($subscribeToSavedSearch);
    }

    /**
     * @param string $class
     */
    private function getMockWithoutConstructorCall($class)
    {
        return $this->getMock($class, array(), array(), '', false);
    }

    /**
     * @return SavedSearches|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSavedSearchesService()
    {
        return $this->getMockWithoutConstructorCall(
            SavedSearches::class
        );
    }

    /**
     * @param SavedSearches $savedSearchesService
     */
    private function getSavedSearchesCommandHandlerWithService(SavedSearches $savedSearchesService)
    {
        $tokenCredentials = new TokenCredentials('token', 'secret');

        // Saved searches service factory used to instantiate the saved searches service.
        $savedSearchesServiceFactory = $this->getMockWithoutConstructorCall(
            SavedSearchesServiceFactory::class
        );
        $savedSearchesServiceFactory->expects($this->once())
            ->method('withTokenCredentials')
            ->with($tokenCredentials)
            ->willReturn($savedSearchesService);

        // Metadata with token credentials, necessary to create the saved searches service.
        $metadataArray = [
            'uitid_token_credentials' => $tokenCredentials,
        ];
        $metadata = $this->getMock(Metadata::class);
        $metadata->expects($this->once())
            ->method('serialize')
            ->willReturn($metadataArray);

        // Command handler with the factory object and context metadata.
        $commandHandler = new SavedSearchesCommandHandler($savedSearchesServiceFactory);
        $commandHandler->setContext($metadata);
        return $commandHandler;
    }
}
