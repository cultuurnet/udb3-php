<?php

namespace CultuurNet\UDB3\SavedSearches\Properties;

use ValueObjects\Web\EmailAddress;

class CreateByQueryStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_creates_a_created_by_query_from_a_user_id()
    {
        $emailAddress = new EmailAddress('foo@bar.com');
        $queryString = new CreatedByQueryString($emailAddress);

        $expected = 'createdby:' . $emailAddress;

        $this->assertEquals($expected, $queryString);
    }
}
