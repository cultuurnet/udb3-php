<?php

namespace CultuurNet\UDB3\Offer\Item;

use CultuurNet\UDB3\Offer\Item\Commands\AddLabel;
use CultuurNet\UDB3\Offer\Item\Commands\DeleteLabel;
use CultuurNet\UDB3\Offer\OfferCommandHandler;

class ItemCommandHandler extends OfferCommandHandler
{
    protected function getAddLabelClassName()
    {
        return AddLabel::class;
    }

    protected function getDeleteLabelClassName()
    {
        return DeleteLabel::class;
    }
}
