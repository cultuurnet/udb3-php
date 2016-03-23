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

    /**
     * @param $itemId
     *  The id of the item that is targeted by the command.
     *
     * @param Label $label
     *  The label that is used in the command.
     */
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
