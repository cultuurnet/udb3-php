<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use CultuurNet\UDB3\Offer\Commands\AbstractAddLabel;
use CultuurNet\UDB3\Offer\Commands\AbstractDeleteLabel;

/**
 * Abstract because it needs a concrete implementation that handles implementations
 * of the abstract commands using the protected methods provided here.
 *
 * @todo Instead of extending Udb3CommandHandler, perhaps create a new
 *   implementation of CommandHandlerInterface which does not rely on the class
 *   name of each first method parameter to figure out which method to call.
 */
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
     * Makes it easier to type-hint to Offer.
     *
     * @param string $id
     * @return Offer
     */
    private function load($id)
    {
        return $this->repository->load($id);
    }

    /**
     * @param AbstractAddLabel $addLabel
     */
    protected function handleAbstractAddLabel(AbstractAddLabel $addLabel)
    {
        $offer = $this->load($addLabel->getItemId());
        $offer->addLabel($addLabel->getLabel());
        $this->repository->save($offer);
    }

    /**
     * @param AbstractDeleteLabel $deleteLabel
     */
    protected function handleAbstractDeleteLabel(AbstractDeleteLabel $deleteLabel)
    {
        $offer = $this->load($deleteLabel->getItemId());
        $offer->deleteLabel($deleteLabel->getLabel());
        $this->repository->save($offer);
    }
}
