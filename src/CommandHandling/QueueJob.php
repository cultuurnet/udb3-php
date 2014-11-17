<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\CommandHandling;


class QueueJob
{

    /**
     * @var \Resque_Job
     */
    public $job;

    public function perform()
    {
        $command = unserialize(base64_decode($this->args['command']));
        global $app;

        /** @var ResqueCommandBus $commandBus */
        $commandBus = $app['event_command_bus'];

        if ($commandBus instanceof ContextAwareInterface) {
            $context = unserialize(base64_decode($this->args['context']));
            $commandBus->setContext($context);
        }

        $commandBus->deferredDispatch($this->job->payload['id'], $command);
    }
}
