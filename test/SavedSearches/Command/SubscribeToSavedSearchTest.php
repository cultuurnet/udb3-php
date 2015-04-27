<?php

namespace CultuurNet\UDB3\SavedSearches\Command;

use \CultureFeed_SavedSearches_SavedSearch as SavedSearch;

class SubscribeToSavedSearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_the_stored_data()
    {
        $userId = 'some-user-id';
        $name = 'My very first saved search.';
        $query = 'city:"Leuven"';

        $command = new SubscribeToSavedSearch($userId, $name, $query);

        $this->assertEquals($userId, $command->getUserId());
        $this->assertEquals($name, $command->getName());
        $this->assertEquals($query, $command->getQuery());
    }
}
