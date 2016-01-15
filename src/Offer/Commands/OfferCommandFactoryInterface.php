<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Label;

interface OfferCommandFactoryInterface
{
    /**
     * @param $id
     * @param Label $label
     * @return object
     */
    public static function createAddLabelCommand($id, Label $label);

    /**
     * @param $id
     * @param Label $label
     * @return object
     */
    public static function createDeleteLabelCommand($id, Label $label);
}
