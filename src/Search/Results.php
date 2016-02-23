<?php

namespace CultuurNet\UDB3\Search;

use CultuurNet\UDB3\Offer\OfferIdentifierInterface;
use ValueObjects\Number\Integer;

class Results
{
    /**
     * @var OfferIdentifierInterface[]
     */
    private $items;

    /**
     * @var Integer
     */
    private $totalItems;

    /**
     * @param OfferIdentifierInterface[] $items
     * @param \ValueObjects\Number\Integer $totalItems
     */
    public function __construct(array $items, Integer $totalItems)
    {
        foreach ($items as $item) {
            $this->guardItemClass($item);
        }

        $this->items = $items;
        $this->totalItems = $totalItems;
    }

    /**
     * @return OfferIdentifierInterface[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return \ValueObjects\Number\Integer
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }

    /**
     * @param mixed $item
     */
    private function guardItemClass($item)
    {
        if (!($item instanceof OfferIdentifierInterface)) {
            throw new \InvalidArgumentException('Each result should be an OfferIdentifierInterface.');
        }
    }
}
