<?php

namespace CultuurNet\UDB3\Place\Commands;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;

class PlaceCommandFactory implements OfferCommandFactoryInterface
{
    /**
     * @param $id
     * @param Label $label
     * @return AddLabel
     */
    public static function createAddLabelCommand($id, Label $label)
    {
        return new AddLabel($id, $label);
    }

    /**
     * @param $id
     * @param Label $label
     * @return DeleteLabel
     */
    public static function createDeleteLabelCommand($id, Label $label)
    {
        return new DeleteLabel($id, $label);
    }
}
