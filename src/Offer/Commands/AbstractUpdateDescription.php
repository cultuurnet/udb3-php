<?php

namespace CultuurNet\UDB3\Offer\Commands;

abstract class AbstractUpdateDescription extends AbstractCommand
{
    /**
     * Description to be added.
     * @var string
     */
    protected $description;

    /**
     * @param string $itemId
     * @param string $description
     */
    public function __construct($itemId, $description)
    {
        parent::__construct($itemId);
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
