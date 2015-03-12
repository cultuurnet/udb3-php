<?php

/**
 * @file
 * Contains CultuurNet\UDB3\OfferEditingTrait.
 */

namespace CultuurNet\UDB3;

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

        $commandClass = $this->getCommandClass('updateTypicalAgeRange');

        return $this->commandBus->dispatch(
            new $commandClass($id, $ageRange)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateOrganizer($id, $organizerId)
    {

        $this->guardId($id);

        $commandClass = $this->getCommandClass('updateOrganizer');

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

        $commandClass = $this->getCommandClass('deleteOrganizer');

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

        $commandClass = $this->getCommandClass('updateContactPoint');

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
    public function addImage($id, MediaObject $mediaObject)
    {

        $this->guardId($id);

        $commandClass = $this->getCommandClass('AddImage');

        return $this->commandBus->dispatch(
            new $commandClass($id, $mediaObject)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateImage($id, $indexToEdit, MediaObject $mediaObject)
    {

        $this->guardId($id);

        $commandClass = $this->getCommandClass('UpdateImage');

        return $this->commandBus->dispatch(
            new $commandClass($id, $indexToEdit, $mediaObject)
        );

    }

    /**
     * {@inheritdoc}
     */
    public function deleteImage($id, $indexToDelete) {

        $this->guardId($id);

        $commandClass = $this->getCommandClass('DeleteImage');

        return $this->commandBus->dispatch(
            new $commandClass($id, $indexToDelete)
        );

    }
}
