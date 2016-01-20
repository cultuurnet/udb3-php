<?php

namespace CultuurNet\UDB3\Offer\Item\ReadModel\JSONLD;

use CultuurNet\UDB3\Offer\Item\Events\LabelAdded;
use CultuurNet\UDB3\Offer\Item\Events\LabelDeleted;
use CultuurNet\UDB3\Offer\ReadModel\JSONLD\OfferLDProjector;

class ItemLDProjector extends OfferLDProjector
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
