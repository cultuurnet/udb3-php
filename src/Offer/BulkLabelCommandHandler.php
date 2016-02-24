<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Commands\AddLabelToMultiple;
use CultuurNet\UDB3\Offer\Commands\AddLabelToQuery;
use CultuurNet\UDB3\Search\ResultsGeneratorInterface;

class BulkLabelCommandHandler extends Udb3CommandHandler
{
    /**
     * @var ResultsGeneratorInterface
     */
    private $resultsGenerator;

    /**
     * @var RepositoryInterface[]
     */
    private $repositories;

    public function __construct(
        ResultsGeneratorInterface $resultsGenerator
    ) {
        $this->resultsGenerator = $resultsGenerator;
        $this->repositories = [];
    }

    /**
     * @param OfferType $offerType
     * @param RepositoryInterface $repository
     * @return BulkLabelCommandHandler
     */
    public function withRepository(OfferType $offerType, RepositoryInterface $repository)
    {
        $c = clone $this;
        $c->repositories[$offerType->toNative()] = $repository;
        return $c;
    }

    /**
     * @param AddLabelToQuery $addLabelToQuery
     */
    public function handleAddLabelToQuery(AddLabelToQuery $addLabelToQuery)
    {
        $label = $addLabelToQuery->getLabel();
        $query = $addLabelToQuery->getQuery();

        foreach ($this->resultsGenerator->search($query) as $result) {
            /* @var OfferIdentifierInterface $result */
            $this->label(
                $result->getType(),
                $result->getId(),
                $label
            );
        }
    }

    /**
     * @param AddLabelToMultiple $addLabelToMultiple
     */
    public function handleAddLabelToMultiple(AddLabelToMultiple $addLabelToMultiple)
    {
        $label = $addLabelToMultiple->getLabel();

        $offerIdentifiers = $addLabelToMultiple->getOfferIdentifiers()
            ->toArray();

        foreach ($offerIdentifiers as $offerIdentifier) {
            $this->label(
                $offerIdentifier->getType(),
                $offerIdentifier->getId(),
                $label
            );
        }
    }

    /**
     * @param OfferType $type
     * @param string $id
     * @param Label $label
     */
    private function label(OfferType $type, $id, Label $label)
    {
        $type = $type->toNative();

        if (!isset($this->repositories[$type])) {
            throw new \LogicException("Found no repository for type {$type}.");
        }

        $repository = $this->repositories[$type];

        /* @var Offer $offer */
        $offer = $repository->load($id);
        $offer->addLabel($label);
        $repository->save($offer);
    }
}
