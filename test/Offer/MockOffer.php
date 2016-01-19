<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Events\MockLabelAdded;
use CultuurNet\UDB3\Offer\Events\MockLabelDeleted;

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
     * @return MockLabelAdded
     */
    protected function createLabelAddedEvent(Label $label)
    {
        return MockLabelAdded::class;
    }

    /**
     * @param Label $label
     * @return MockLabelDeleted
     */
    protected function createLabelDeletedEvent(Label $label)
    {
        return MockLabelDeleted::class;
    }

    /**
     * @return mixed
     */
    public function getAggregateRootId()
    {
        return $this->id;
    }
}
