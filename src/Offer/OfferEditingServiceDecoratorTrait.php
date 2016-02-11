<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Label;

trait OfferEditingServiceDecoratorTrait
{
    /**
     * @return OfferEditingServiceInterface
     */
    abstract protected function getDecoratedEditingService();

    /**
     * @param $id
     * @param Label $label
     */
    public function addLabel($id, Label $label)
    {
        $this->getDecoratedEditingService()
            ->addLabel($id, $label);
    }

    /**
     * @param $id
     * @param Label $label
     */
    public function deleteLabel($id, Label $label)
    {
        $this->getDecoratedEditingService()
            ->deleteLabel($id, $label);
    }
}
