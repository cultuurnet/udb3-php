<?php

namespace CultuurNet\UDB3\Offer\Item\ReadModel\History;

use CultuurNet\UDB3\Offer\Item\Events\LabelAdded;
use CultuurNet\UDB3\Offer\Item\Events\LabelDeleted;
use CultuurNet\UDB3\Offer\ReadModel\History\OfferHistoryProjector;

class ItemHistoryProjector extends OfferHistoryProjector
{

    /**
     * @return string
     */
    protected function getLabelAddedClassName()
    {
        return LabelAdded::class;
    }

    /**
     * @return string
     */
    protected function getLabelDeletedClassName()
    {
        return LabelDeleted::class;
    }
}
