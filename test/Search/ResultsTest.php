<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Search;


use ValueObjects\Number\Integer;

class ResultsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_instantiated_with_result_items_and_total()
    {
        $items = [1, 2, 3, 4];
        $totalItems = new Integer(20);

        $results = new Results($items, $totalItems);

        $this->assertEquals($items, $results->getItems());
        $this->assertEquals($totalItems, $results->getTotalItems());
    }

    /**
     * @test
     *
     * @expectedException PHPUnit_Framework_Error
     */
    public function it_only_accepts_an_items_array()
    {
        new Results('foo', new Integer(5));
    }

    /**
     * @test
     *
     * @expectedException PHPUnit_Framework_Error
     */
    public function it_only_accepts_a_total_items_integer()
    {
        new Results(['foo'], 'foo');
    }
}
