<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Media\Image;
use ValueObjects\String\String as VOString;

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
        return $this->getDecoratedEditingService()
            ->addLabel($id, $label);
    }

    /**
     * @param $id
     * @param Label $label
     */
    public function deleteLabel($id, Label $label)
    {
        return $this->getDecoratedEditingService()
            ->deleteLabel($id, $label);
    }

    /**
     * @param $id
     * @param Language $language
     * @param VOString $title
     */
    public function translateTitle($id, Language $language, VOString $title)
    {
        return $this->getDecoratedEditingService()
            ->translateTitle($id, $language, $title);
    }

    /**
     * @param $id
     * @param Language $language
     * @param VOString $description
     */
    public function translateDescription($id, Language $language, VOString $description)
    {
        return $this->getDecoratedEditingService()
            ->translateDescription($id, $language, $description);
    }

    public function selectMainImage($id, Image $image)
    {
        return $this->getDecoratedEditingService()
            ->selectMainImage($id, $image);
    }
}
