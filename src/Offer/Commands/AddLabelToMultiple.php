<?php


namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\Entry\Keyword;

class AddLabelToMultiple
{
    /**
     * @var array
     */
    protected $offerIds;

    /**
     * @var Keyword
     */
    protected $label;

    public function __construct($offerIds, Keyword $label)
    {
        $this->offerIds = $offerIds;
        $this->label = $label;
    }

    public function getOfferIds()
    {
        return $this->offerIds;
    }

    /**
     * @return Keyword
     */
    public function getLabel()
    {
        return $this->label;
    }
}
