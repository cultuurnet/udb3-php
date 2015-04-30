<?php

namespace CultuurNet\UDB3\SavedSearches\Properties;

use ValueObjects\String\String;

class CreateByQueryStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_creates_a_created_by_query_from_a_user_id()
    {
        $userId = new String('some-user-id');
        $queryString = new CreatedByQueryString($userId);

        $expected = 'createdby:' . $userId;

        $this->assertEquals($expected, $queryString);
    }
}
