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
            '{"name":"In Leuven","query":"city:\"Leuven\"","id":"101"}',
            $jsonEncoded
        );
    }

    /**
     * @test
     */
    public function it_does_not_serialize_an_empty_id_property()
    {
        $savedSearch = new SavedSearch(
            new String('In Leuven'),
            new QueryString('city:"Leuven"')
        );

        $jsonEncoded = json_encode($savedSearch);

        $this->assertEquals(
            '{"name":"In Leuven","query":"city:\"Leuven\""}',
            $jsonEncoded
        );
    }
}
