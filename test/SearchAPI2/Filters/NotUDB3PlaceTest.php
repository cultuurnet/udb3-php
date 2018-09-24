<?php

namespace CultuurNet\UDB3\SearchAPI2\Filters;

use CultuurNet\Search\Parameter\FilterQuery;

class NotUDB3PlaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_add_a_filter_to_remove_udb2_events_marked_as_udb3_places()
    {
        $existingFilterQuery = new FilterQuery('some:"existing filter"');
        $searchParameters = [$existingFilterQuery];
        $UDB3PlacesFilter = new NotUDB3Place();
        $filterQuery = new FilterQuery('!keywords: "udb3 place"');
        $expectedFilters = [
            $existingFilterQuery,
            $filterQuery,
        ];

        $filteringSearchParameters = $UDB3PlacesFilter->apply($searchParameters);
        $this->assertEquals($expectedFilters, $filteringSearchParameters);
    }
}
