<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\CommandHandling;


use Broadway\CommandHandling\CommandBusInterface;
use Broadway\CommandHandling\CommandHandlerInterface;
use Broadway\CommandHandling\SimpleCommandBus;

/**
 * Command bus for asynchronous processing with PHP-Resque
 */
class ResqueCommandBus extends SimpleCommandBus {

    /**
     * @var string
     */
    protected $queueName;

    public function __construct($queueName)
    {
        $this->queueName = $queueName;
    }

    /**
     * Dispatches the command $command to a queue.
     *
     * @param mixed $command
     */
    public function dispatch($command)
    {
        $args = array();
        $args['command'] = serialize($command);
        $id = \Resque::enqueue(
            $this->queueName,
            '\\CultuurNet\\UDB3\\CommandHandling\\QueueJob',
            $args,
            true
        );

        return $id;
    }

    /**
     * Really dispatches the command to the proper handler to be executed.
     */
    public function deferredDispatch($command)
    {
        parent::dispatch($command);
    }
} 
