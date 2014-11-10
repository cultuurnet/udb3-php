<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\CommandHandling;


use Broadway\CommandHandling\CommandBusInterface;
use Broadway\CommandHandling\CommandHandlerInterface;
use Broadway\Domain\Metadata;

class SimpleContextAwareCommandBus implements CommandBusInterface, ContextAwareInterface
{
    /**
     * @var Metadata
     */
    protected $context;

    /**
     * @var CommandHandlerInterface
     */
    private $commandHandlers = array();

    public function setContext(Metadata $context = null)
    {
        $this->context = $context;
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe(CommandHandlerInterface $handler)
    {
        $this->commandHandlers[] = $handler;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch($command)
    {
        /** @var CommandHandlerInterface|ContextAwareInterface $handler */
        foreach ($this->commandHandlers as $handler) {
            if ($handler instanceof ContextAwareInterface) {
                $handler->setContext($this->context);
            }
            $handler->handle($command);
        }
    }
} 
