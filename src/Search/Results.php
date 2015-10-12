<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Search;

use ValueObjects\Number\Integer;

class Results
{
    /**
     * @var array
     */
    private $items;

    /**
     * @var Integer
     */
    private $totalItems;

    /**
     * @param array $items
     * @param Integer $totalItems
     */
    public function __construct(array $items, Integer $totalItems)
    {
        $this->items = $items;
        $this->totalItems = $totalItems;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return Integer
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }
}
