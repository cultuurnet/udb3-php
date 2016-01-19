<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Events\LabelAdded;
use CultuurNet\UDB3\Offer\Events\LabelDeleted;

class MockOffer extends Offer
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @param mixed $id
     */
    public function __construct($id)
    {
        parent::__construct();
        $this->id = $id;
    }

    /**
     * @param Label $label
     * @return LabelAdded
     */
    protected function createLabelAddedEvent(Label $label)
    {
        return new LabelAdded($this->id, $label);
    }

    /**
     * @param Label $label
     * @return LabelDeleted
     */
    protected function createLabelDeletedEvent(Label $label)
    {
        return new LabelDeleted($this->id, $label);
    }

    /**
     * @return mixed
     */
    public function getAggregateRootId()
    {
        return $this->id;
    }
}
