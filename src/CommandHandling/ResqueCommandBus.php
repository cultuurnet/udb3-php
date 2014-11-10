<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\CommandHandling;


use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Domain\Metadata;
use Broadway\EventDispatcher\EventDispatcherInterface;

/**
 * Command bus decorator for asynchronous processing with PHP-Resque.
 */
class ResqueCommandBus extends CommandBusDecoratorBase implements ContextAwareInterface
{

    const EVENT_COMMAND_CONTEXT_SET = 'broadway.command_handling.context';

    /**
     * @var CommandBusInterface|ContextAwareInterface
     */
    protected $decoratee;

    /**
     * @var Metadata
     */
    protected $context;

    /**
     * @var string
     */
    protected $queueName;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param CommandBusInterface $decoratee
     * @param string $queueName
     */
    public function __construct(
        CommandBusInterface $decoratee,
        $queueName,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($decoratee);
        $this->queueName = $queueName;
        $this->eventDispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(Metadata $context = null)
    {
        $this->context = $context;

        if ($this->decoratee instanceof ContextAwareInterface) {
            $this->decoratee->setContext($this->context);
        }

        $this->eventDispatcher->dispatch(
            self::EVENT_COMMAND_CONTEXT_SET,
            array(
                'context' => $this->context,
            )
        );
    }

    /**
     * Get the current execution context.
     *
     * @return Metadata
     */
    public function getContext()
    {
        return $this->context;
    }


    /**
     * Dispatches the command $command to a queue.
     *
     * @param mixed $command
     *
     * @return string the command id
     */
    public function dispatch($command)
    {
        $args = array();
        $args['command'] = serialize($command);
        $args['context'] = serialize($this->context);
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
        try {
            parent::dispatch($command);

            // Reset the execution context after each command.
            $this->setContext(null);
        } catch (\Exception $e) {
            // Reset the execution context after each command.
            $this->setContext(null);
            throw $e;
        }
    }
}
