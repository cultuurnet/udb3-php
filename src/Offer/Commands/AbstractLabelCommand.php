<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Label;

abstract class AbstractLabelCommand
{
    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var Label
     */
    protected $label;

    public function __construct($itemId, Label $label)
    {
        $this->label = $label;
        $this->itemId = $itemId;
    }

    /**
     * @return string
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @return Label
     */
    public function getLabel()
    {
        return $this->label;
    }
}
