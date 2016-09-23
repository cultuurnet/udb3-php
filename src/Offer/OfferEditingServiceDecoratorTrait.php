<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\PriceInfo\PriceInfo;
use ValueObjects\String\String as StringLiteral;

trait OfferEditingServiceDecoratorTrait
{
    /**
     * @return OfferEditingServiceInterface
     */
    abstract protected function getDecoratedEditingService();

    public function addLabel($id, Label $label)
    {
        return $this->getDecoratedEditingService()
            ->addLabel($id, $label);
    }

    public function deleteLabel($id, Label $label)
    {
        return $this->getDecoratedEditingService()
            ->deleteLabel($id, $label);
    }

    public function translateTitle($id, Language $language, StringLiteral $title)
    {
        return $this->getDecoratedEditingService()
            ->translateTitle($id, $language, $title);
    }

    public function translateDescription($id, Language $language, StringLiteral $description)
    {
        return $this->getDecoratedEditingService()
            ->translateDescription($id, $language, $description);
    }

    public function addImage($id, Image $image)
    {
        return $this->getDecoratedEditingService()
            ->addImage($id, $image);
    }

    public function updateImage($id, Image $image, StringLiteral $description, StringLiteral $copyrightHolder)
    {
        return $this->getDecoratedEditingService()
            ->updateImage($id, $image, $description, $copyrightHolder);
    }

    public function removeImage($id, Image $image)
    {
        return $this->getDecoratedEditingService()
            ->removeImage($id, $image);
    }

    public function selectMainImage($id, Image $image)
    {
        return $this->getDecoratedEditingService()
            ->selectMainImage($id, $image);
    }

    public function updateDescription($id, $description)
    {
        return $this->getDecoratedEditingService()
            ->updateDescription($id, $description);
    }

    public function updateTypicalAgeRange($id, $ageRange)
    {
        return $this->getDecoratedEditingService()
            ->updateTypicalAgeRange($id, $ageRange);
    }

    public function deleteTypicalAgeRange($id)
    {
        return $this->getDecoratedEditingService()
            ->deleteTypicalAgeRange($id);
    }

    public function updateOrganizer($id, $organizerId)
    {
        return $this->getDecoratedEditingService()
            ->updateOrganizer($id, $organizerId);
    }

    public function deleteOrganizer($id, $organizerId)
    {
        return $this->getDecoratedEditingService()
            ->deleteOrganizer($id, $organizerId);
    }

    public function updateContactPoint($id, ContactPoint $contactPoint)
    {
        return $this->getDecoratedEditingService()
            ->updateContactPoint($id, $contactPoint);
    }

    public function updateBookingInfo($id, BookingInfo $bookingInfo)
    {
        return $this->getDecoratedEditingService()
            ->updateBookingInfo($id, $bookingInfo);
    }

    public function updatePriceInfo($id, PriceInfo $priceInfo)
    {
        return $this->getDecoratedEditingService()
            ->updatePriceInfo($id, $priceInfo);
    }
}
