<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventHandling;

use Broadway\Domain\DomainMessage;

trait DelegateEventHandlingToSpecificMethodTrait
{
    /**
     * {@inheritDoc}
     */
    public function handle(DomainMessage $domainMessage)
    {
        $event  = $domainMessage->getPayload();
        $method = $this->getHandleMethod($event);

        if (!method_exists($this, $method)) {
            return;
        }

        try {
            $parameter = new \ReflectionParameter(array($this, $method), 0);
        } catch (\ReflectionException $e) {
            // No parameter for the method, so we ignore it.
            return;
        }
        $expectedClass = $parameter->getClass();

        if ($expectedClass->getName() == get_class($event)) {
            $this->$method($event, $domainMessage);
        }
    }

    private function getHandleMethod($event)
    {
        $classParts = explode('\\', get_class($event));

        return 'apply' . end($classParts);
    }
}
