<?php

namespace CultuurNet\UDB3\MyOrganizers;

use PHPUnit\Framework\TestCase;
use ValueObjects\Number\Natural;

class PartOfCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function its_a_container_for_items_and_total_count()
    {
        $items = ['foo', 'bar'];

        $collectionA = new PartOfCollection(
            $items,
            new Natural(100)
        );

        $this->assertEquals(
            new Natural(100),
            $collectionA->getTotal()
        );

        $this->assertEquals(
            $items,
            $collectionA->getItems()
        );
    }
}
