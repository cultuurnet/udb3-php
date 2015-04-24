<?php

namespace CultuurNet\UDB3\SavedSearches;

use \CultureFeed_SavedSearches_Default as SavedSearches;
use \CultureFeed_SavedSearches_SavedSearch as SavedSearch;
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
        $savedSearchesService = $this->getMock(
            SavedSearches::class,
            array(),
            array(),
            '',
            false
        );
        $savedSearchesService->expects($this->once())
            ->method('subscribe')
            ->with($savedSearch);

        // Command handler.
        $commandHandler = new SavedSearchesCommandHandler($savedSearchesService);

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
        $savedSearchesService = $this->getMock(
            SavedSearches::class,
            array(),
            array(),
            '',
            false
        );
        $savedSearchesService->expects($this->once())
            ->method('subscribe')
            ->willThrowException(new \CultureFeed_Exception($error, 'UNKNOWN_ERROR'));

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

        // Command handler with logger.
        $commandHandler = new SavedSearchesCommandHandler($savedSearchesService);
        $commandHandler->setLogger($logger);

        // Handle the "subscribe to saved search" command.
        // This will result in an exception being thrown, and logged.
        $commandHandler->handle($subscribeToSavedSearch);
    }
}
