<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Label;

interface OfferEditingServiceInterface
{
    /**
     * @param $id
     * @param Label $label
     */
    public function addLabel($id, Label $label);

    /**
     * @param $id
     * @param Label $label
     */
    public function deleteLabel($id, Label $label);
}
