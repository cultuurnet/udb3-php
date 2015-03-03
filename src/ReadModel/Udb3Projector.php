<?php

/**
 * @file
 * Contains \Udb3Projector.
 */

namespace CultuurNet\UDB3\ReadModel;

use Broadway\Domain\DomainMessageInterface;
use Broadway\EventHandling\EventListenerInterface;

/**
 * Handles events and projects to a read model.
 */
abstract class Udb3Projector implements EventListenerInterface
{

    /**
     * {@inheritDoc}
     */
    public function handle(DomainMessageInterface $domainMessage)
    {
        $event  = $domainMessage->getPayload();
        $method = $this->getHandleMethod($event);

        if (! method_exists($this, $method)) {
            return;
        }

        $parameter = new \ReflectionParameter(array($this, $method), 0);
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
