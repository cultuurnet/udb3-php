<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use CultuurNet\UDB3\Offer\Commands\AbstractAddLabel;
use CultuurNet\UDB3\Offer\Commands\AbstractDeleteLabel;

abstract class OfferCommandHandler extends Udb3CommandHandler
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($command)
    {
        $commandName = get_class($command);
        $commandHandlers = $this->getCommandHandlers();

        if (isset($commandHandlers[$commandName])) {
            $handler = $commandHandlers[$commandName];
            call_user_func(array($this, $handler), $command);
        } else {
            parent::handle($command);
        }
    }

    /**
     * @return string[]
     *   An associative array of commands and their handler methods.
     */
    private function getCommandHandlers()
    {
        $commands = [];

        foreach (get_class_methods($this) as $method) {
            $matches = [];
            if (preg_match('/^handle(.+)$/', $method, $matches)) {
                $command = $matches[1];
                $classNameMethod = 'get' . $command . 'ClassName';

                if (!method_exists($this, $classNameMethod)) {
                    continue;
                }

                $commandFullClassName = call_user_func(array($this, $classNameMethod));
                $commands[$commandFullClassName] = $method;
            }
        }

        return $commands;
    }

    /**
     * @return string
     */
    abstract protected function getAddLabelClassName();

    /**
     * @return string
     */
    abstract protected function getDeleteLabelClassName();

    /**
     * @param AbstractAddLabel $addLabel
     */
    private function handleAddLabel(AbstractAddLabel $addLabel)
    {
        $offer = $this->load($addLabel->getItemId());
        $offer->addLabel($addLabel->getLabel());
        $this->repository->save($offer);
    }

    /**
     * @param AbstractDeleteLabel $deleteLabel
     */
    private function handleDeleteLabel(AbstractDeleteLabel $deleteLabel)
    {
        $offer = $this->load($deleteLabel->getItemId());
        $offer->deleteLabel($deleteLabel->getLabel());
        $this->repository->save($offer);
    }

    /**
     * Makes it easier to type-hint to Offer.
     *
     * @param string $id
     * @return Offer
     */
    private function load($id)
    {
        return $this->repository->load($id);
    }
}
