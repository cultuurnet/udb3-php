<?php

/**
 * @file
 * Contains CultuurNet\UDB3\OfferEditingTrait.
 */

namespace CultuurNet\UDB3;

/**
 * Trait that contains all major editing methods for Offers.
 */
trait OfferEditingTrait {

    /**
     * Get the namespaced classname of the command to create.
     * @param type $className
     *   Name of the class
     * @return string
     */
    private function getCommandClass($className) {
      $reflection = new \ReflectionObject($this);
      return $reflection->getNamespaceName() . '\\' . $className;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDescription($id, $description) {

      $this->guardId($id);

      $commandClass = $this->getCommandClass('UpdateDescription');

      return $this->commandBus->dispatch(
          new $commandClass($id, $description)
      );

    }

    /**
     * {@inheritdoc}
     */
    public function updateTypicalAgeRange($id, $ageRange) {

      $this->guardId($id);

      $commandClass = $this->getCommandClass('UpdateDescription');

      return $this->commandBus->dispatch(
          new UpdateTypicalAgeRange($id, $ageRange)
      );

    }

}
