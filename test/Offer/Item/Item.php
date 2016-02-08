<?php

namespace CultuurNet\UDB3\Offer\Item;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Item\Events\ItemCreated;
use CultuurNet\UDB3\Offer\Item\Events\LabelAdded;
use CultuurNet\UDB3\Offer\Item\Events\LabelDeleted;
use CultuurNet\UDB3\Offer\Offer;

class Item extends Offer
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @param ItemCreated $created
     */
    protected function applyItemCreated(ItemCreated $created)
    {
        $this->id = $created->getItemId();
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
