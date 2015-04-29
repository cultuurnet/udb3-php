<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SavedSearches\ReadModel;

use CultuurNet\UDB3\SavedSearches\Properties\QueryString;
use ValueObjects\String\String;

class SavedSearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_serialized_to_json()
    {
        $savedSearch = new SavedSearch(
            new String('In Leuven'),
            new QueryString('city:"Leuven"'),
            new String('101')
        );

        $jsonEncoded = json_encode($savedSearch);

        $this->assertEquals(
            '{"id":"101","name":"In Leuven","query":"city:\"Leuven\""}',
            $jsonEncoded
        );
    }
}
