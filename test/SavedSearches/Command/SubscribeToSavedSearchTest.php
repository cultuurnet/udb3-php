<?php

namespace CultuurNet\UDB3\SavedSearches\Command;

use CultuurNet\UDB3\SavedSearches\Properties\QueryString;
use ValueObjects\StringLiteral\StringLiteral;

class SubscribeToSavedSearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_the_stored_data()
    {
        $userId = new StringLiteral('some-user-id');
        $name = new StringLiteral('My very first saved search.');
        $query = new QueryString('city:"Leuven"');

        $command = new SubscribeToSavedSearch($userId, $name, $query);

        $this->assertEquals($userId, $command->getUserId());
        $this->assertEquals($name, $command->getName());
        $this->assertEquals($query, $command->getQuery());
    }
}
