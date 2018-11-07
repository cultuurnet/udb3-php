<?php

namespace CultuurNet\UDB3\SavedSearches\Command;

use CultuurNet\UDB3\ValueObject\SapiVersion;
use ValueObjects\StringLiteral\StringLiteral;

class UnsubscribeFromSavedSearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_the_stored_data()
    {
        $sapiVersion = new SapiVersion(SapiVersion::V2);
        $userId = new StringLiteral('some-user-id');
        $searchId = new StringLiteral('some-search-id');

        $command = new UnsubscribeFromSavedSearch($sapiVersion, $userId, $searchId);

        $this->assertEquals($sapiVersion, $command->getSapiVersion());
        $this->assertEquals($userId, $command->getUserId());
        $this->assertEquals($searchId, $command->getSearchId());
    }
}
