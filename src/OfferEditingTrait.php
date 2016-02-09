<?php

/**
 * @file
 * Contains CultuurNet\UDB3\OfferEditingTrait.
 */

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Media\MediaObject;
use ValueObjects\String\String;

/**
 * Trait that contains all major editing methods for Offers.
 */
trait OfferEditingTrait
{

    /**
     * Get the namespaced classname of the command to create.
     * @param type $className
     *   Name of the class
     * @return string
     */
    private function getCommandClass($className)
    {
        $reflection = new \ReflectionObject($this);
        return $reflection->getNamespaceName() . '\\Commands\\' . $className;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDescription($id, $description)
    {

        $this->guardId($id);

        $commandClass = $this->getCommandClass('UpdateDescription');

        return $this->commandBus->dispatch(
            new $commandClass($id, $description)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateTypicalAgeRange($id, $ageRange)
    {

        $this->guardId($id);

        $commandClass = $this->getCommandClass('UpdateTypicalAgeRange');

        return $this->commandBus->dispatch(
            new $commandClass($id, $ageRange)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTypicalAgeRange($id)
    {

        $this->guardId($id);

        $commandClass = $this->getCommandClass('DeleteTypicalAgeRange');

        return $this->commandBus->dispatch(
            new $commandClass($id)
        );

    }

    /**
     * {@inheritdoc}
     */
    public function updateOrganizer($id, $organizerId)
    {

        $this->guardId($id);

        $commandClass = $this->getCommandClass('UpdateOrganizer');

        return $this->commandBus->dispatch(
            new $commandClass($id, $organizerId)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteOrganizer($id, $organizerId)
    {

        $this->guardId($id);

        $commandClass = $this->getCommandClass('DeleteOrganizer');

        return $this->commandBus->dispatch(
            new $commandClass($id, $organizerId)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateContactPoint($id, ContactPoint $contactPoint)
    {

        $this->guardId($id);

        $commandClass = $this->getCommandClass('UpdateContactPoint');

        return $this->commandBus->dispatch(
            new $commandClass($id, $contactPoint)
        );

    }

    /**
     * {@inheritdoc}
     */
    public function updateBookingInfo($id, BookingInfo $bookingInfo)
    {

        $this->guardId($id);

        $commandClass = $this->getCommandClass('UpdateBookingInfo');

        return $this->commandBus->dispatch(
            new $commandClass($id, $bookingInfo)
        );

    }

    /**
     * {@inheritdoc}
     */
    public function addImage($id, Image $image)
    {
        $this->guardId($id);

        $commandClass = $this->getCommandClass('AddImage');

        return $this->commandBus->dispatch(
            new $commandClass($id, $image)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateImage(
        $id,
        Image $image,
        String $description,
        String $copyrightHolder
    ) {
        $this->guardId($id);

        $commandClass = $this->getCommandClass('UpdateImage');

        return $this->commandBus->dispatch(
            new $commandClass(
                $id,
                $image->getMediaObjectId(),
                $description,
                $copyrightHolder
            )
        );
    }

    /**
     * @param $id
     *  Id of the offer to remove the image from.
     *
     * @param Image $image
     *  The image that should be removed.
     *
     * @return mixed
     */
    public function removeImage($id, Image $image)
    {
        $this->guardId($id);

        $commandClass = $this->getCommandClass('RemoveImage');

        return $this->commandBus->dispatch(
            new $commandClass($id, $image)
        );

    }
}
