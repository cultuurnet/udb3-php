<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Category;

abstract class AbstractUpdateType extends AbstractCommand
{
    /**
     * @var Category
     */
    protected $type;

    /**
     * @param string $itemId
     * @param Category $type
     */
    public function __construct($itemId, Category $type)
    {
        parent::__construct($itemId);
        $this->type = $type;
    }

    /**
     * @return Category
     */
    public function getType()
    {
        return $this->type;
    }
}
