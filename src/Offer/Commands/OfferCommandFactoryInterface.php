<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Label;

interface OfferCommandFactoryInterface
{
    /**
     * @param $id
     * @param Label $label
     * @return AbstractAddLabel
     */
    public function createAddLabelCommand($id, Label $label);

    /**
     * @param $id
     * @param Label $label
     * @return AbstractDeleteLabel
     */
    public function createDeleteLabelCommand($id, Label $label);
}
