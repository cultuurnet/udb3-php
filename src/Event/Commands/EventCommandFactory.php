<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Commands\AbstractAddLabel;
use CultuurNet\UDB3\Offer\Commands\AbstractDeleteLabel;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;

class EventCommandFactory implements OfferCommandFactoryInterface
{
    /**
     * @param $id
     * @param Label $label
     * @return AbstractAddLabel
     */
    public function createAddLabelCommand($id, Label $label)
    {
        return new AddLabel($id, $label);
    }

    /**
     * @param $id
     * @param Label $label
     * @return AbstractDeleteLabel
     */
    public function createDeleteLabelCommand($id, Label $label)
    {
        return new DeleteLabel($id, $label);
    }
}
