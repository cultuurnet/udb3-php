<?php

namespace CultuurNet\UDB3\MyOrganizers;

use ValueObjects\Number\Natural;

class PartOfCollection
{
    /**
     * @var array
     */
    private $items;

    /**
     * @var Natural
     */
    private $total;

    public function __construct(
        array $items,
        Natural $total
    ) {
        $this->items = $items;
        $this->total = $total;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return Natural
     */
    public function getTotal(): Natural
    {
        return $this->total;
    }
}
