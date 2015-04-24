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
        $this->assertEquals(SavedSearch::NEVER, $command->getFrequency());

        $command = new SubscribeToSavedSearch($userId, $name, $query, SavedSearch::DAILY);
        $this->assertEquals(SavedSearch::DAILY, $command->getFrequency());
    }

    /**
     * @test
     */
    public function it_validates_the_frequency()
    {
        $userId = 'some-user-id';
        $name = 'My very first saved search.';
        $query = 'city:"Leuven"';
        $invalidFrequency = 'SOMETIMES';

        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Invalid value for frequency: ' . $invalidFrequency
        );
        new SubscribeToSavedSearch($userId, $name, $query, $invalidFrequency);
    }
}
