<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\BookingInfo;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Media\Image;
use ValueObjects\String\String as StringLiteral;

interface OfferEditingServiceInterface
{
    /**
     * @param $id
     * @param Label $label
     * @return string
     */
    public function addLabel($id, Label $label);

    /**
     * @param $id
     * @param Label $label
     * @return string
     */
    public function deleteLabel($id, Label $label);

    /**
     * @param $id
     * @param Language $language
     * @param StringLiteral $title
     * @return string
     */
    public function translateTitle($id, Language $language, StringLiteral $title);

    /**
     * @param $id
     * @param Language $language
     * @param StringLiteral $description
     * @return string
     */
    public function translateDescription($id, Language $language, StringLiteral $description);

    /**
     * @param string $id
     * @param Image $image
     * @return string
     */
    public function addImage($id, Image $image);

    /**
     * @param string $id
     * @param Image $image
     * @param StringLiteral $description
     * @param StringLiteral $copyrightHolder
     * @return string
     */
    public function updateImage($id, Image $image, StringLiteral $description, StringLiteral $copyrightHolder);

    /**
     * @param $id
     *  Id of the offer to remove the image from.
     *
     * @param Image $image
     *  The image that should be removed.
     *
     * @return string
     */
    public function removeImage($id, Image $image);

    /**
     * @param $id
     * @param Image $image
     * @return string
     */
    public function selectMainImage($id, Image $image);

    /**
     * @param string $id
     * @param string $description
     * @return string
     */
    public function updateDescription($id, $description);

    /**
     * @param string $id
     * @param string $ageRange
     * @return string
     */
    public function updateTypicalAgeRange($id, $ageRange);

    /**
     * @param string $id
     * @return string
     */
    public function deleteTypicalAgeRange($id);

    /**
     * @param string $id
     * @param string $organizerId
     * @return string
     */
    public function updateOrganizer($id, $organizerId);

    /**
     * @param string $id
     * @param string $organizerId
     * @return string
     */
    public function deleteOrganizer($id, $organizerId);

    /**
     * @param string $id
     * @param ContactPoint $contactPoint
     * @return string
     */
    public function updateContactPoint($id, ContactPoint $contactPoint);

    /**
     * @param string $id
     * @param BookingInfo $bookingInfo
     * @return string
     */
    public function updateBookingInfo($id, BookingInfo $bookingInfo);
}
