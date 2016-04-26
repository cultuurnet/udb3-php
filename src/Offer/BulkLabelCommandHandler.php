<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Commands\AddLabelToMultiple;
use CultuurNet\UDB3\Offer\Commands\AddLabelToQuery;
use CultuurNet\UDB3\Search\ResultsGeneratorInterface;
use CultuurNet\UDB3\Variations\AggregateDeletedException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class BulkLabelCommandHandler extends Udb3CommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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

        $this->setLogger(new NullLogger());
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
            /* @var IriOfferIdentifier $result */
            $this->label(
                $result->getType(),
                $result->getId(),
                $label,
                AddLabelToQuery::class
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
                $label,
                AddLabelToMultiple::class
            );
        }
    }

    /**
     * @param OfferType $type
     * @param string $id
     * @param Label $label
     * @param string|null $originalCommandName
     *   Original command name, for logging purposes if an entity is not found.
     */
    private function label(OfferType $type, $id, Label $label, $originalCommandName = null)
    {
        $type = $type->toNative();

        if (!isset($this->repositories[$type])) {
            throw new \LogicException("Found no repository for type {$type}.");
        }

        $repository = $this->repositories[$type];

        $logContext = [
            'id' => $id,
            'type' => $type,
            'command' => $originalCommandName,
        ];

        /* @var Offer $offer */
        try {
            $offer = $repository->load($id);
            $offer->addLabel($label);
            $repository->save($offer);
        } catch (AggregateNotFoundException $e) {
            $this->logger->error('bulk_label_command_entity_not_found', $logContext);
        } catch (AggregateDeletedException $e) {
            $this->logger->error('bulk_label_command_entity_deleted', $logContext);
        }
    }
}
