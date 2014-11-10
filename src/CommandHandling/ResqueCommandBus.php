<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\CommandHandling;


use Broadway\CommandHandling\CommandBusInterface;
use Broadway\CommandHandling\CommandHandlerInterface;
use CultuurNet\UDB3\Log\ContextEnrichingLogger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Command bus for asynchronous processing with PHP-Resque
 */
class ResqueCommandBus implements CommandBusInterface, LoggerAwareInterface
{

    use LoggerAwareTrait;

    /**
     * @var string
     */
    protected $queueName;

    /**
     * @var CommandHandlerInterface[]
     */
    protected $commandHandlers = array();


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
     *
     * @param string $jobId
     * @param mixed $command
     */
    public function deferredDispatch($jobId, $command)
    {
        $currentCommandLogger = null;
        if ($this->logger) {
            $jobMetadata = array(
                'job_id' => $jobId,
            );
            $currentCommandLogger = new ContextEnrichingLogger(
                $this->logger,
                $jobMetadata
            );
        }

        if ($currentCommandLogger) {
            $currentCommandLogger->info('job_started');
        }

        foreach ($this->commandHandlers as $handler) {
            if ($currentCommandLogger && $handler instanceof LoggerAwareInterface) {
                $handler->setLogger($currentCommandLogger);
            }

            $handler->handle($command);
        }

        if ($currentCommandLogger) {
            $currentCommandLogger->info('job_finished');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe(CommandHandlerInterface $handler)
    {
        $this->commandHandlers[] = $handler;
    }


} 
