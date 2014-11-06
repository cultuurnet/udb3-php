<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\CommandHandling;


class QueueJob {

    public function perform()
    {
        $command = unserialize($this->args['command']);
        global $app;

        /** @var ResqueCommandBus $commandBus */
        $commandBus = $app['event_command_bus'];

        if ($commandBus instanceof ContextAwareInterface) {
            $context = unserialize($this->args['context']);
            $commandBus->setContext($context);
        }

        $commandBus->deferredDispatch($command);
    }
} 
