<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\SavedSearches\ReadModel;

class SavedSearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_serialized_to_json()
    {
        $savedSearch = new SavedSearch('In Leuven', 'city:"Leuven"', '101');

        $jsonEncoded = json_encode($savedSearch);

        $this->assertEquals(
            '{"id":"101","name":"In Leuven","query":"city:\"Leuven\""}',
            $jsonEncoded
        );
    }
}
