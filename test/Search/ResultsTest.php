<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Search;

use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\OfferIdentifierCollection;
use CultuurNet\UDB3\Offer\OfferType;
use ValueObjects\Number\Integer;

class ResultsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_instantiated_with_result_items_and_total()
    {
        $items = OfferIdentifierCollection::fromArray(
            [
                new IriOfferIdentifier(
                    'event/1',
                    OfferType::EVENT()
                ),
                new IriOfferIdentifier(
                    'event/2',
                    OfferType::EVENT()
                ),
                new IriOfferIdentifier(
                    'event/3',
                    OfferType::EVENT()
                ),
                new IriOfferIdentifier(
                    'event/4',
                    OfferType::EVENT()
                ),
            ]
        );
        $totalItems = new Integer(20);

        $results = new Results($items, $totalItems);

        $this->assertEquals($items->toArray(), $results->getItems());
        $this->assertEquals($totalItems, $results->getTotalItems());
    }

    /**
     * @test
     *
     * @expectedException \PHPUnit_Framework_Error
     */
    public function it_only_accepts_an_items_array()
    {
        new Results('foo', new Integer(5));
    }

    /**
     * @test
     *
     * @expectedException \PHPUnit_Framework_Error
     */
    public function it_only_accepts_a_total_items_integer()
    {
        new Results(
            OfferIdentifierCollection::fromArray(
                [
                    new IriOfferIdentifier('event/1', OfferType::EVENT())
                ]
            ),
            'foo'
        );
    }
}
