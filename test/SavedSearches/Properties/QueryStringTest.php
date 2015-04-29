<?php

namespace CultuurNet\UDB3\SavedSearches\Properties;

class QueryStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_get_a_query_string_from_a_url_query_string()
    {
        $urlQueryString = 'a=b&q=city:leuven&c=d';
        $expected = 'city:leuven';

        $queryString = QueryString::fromURLQueryString($urlQueryString);

        $this->assertEquals($expected, $queryString);
        $this->assertEquals($expected, $queryString->toNative());
    }
}
