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
}
