<?php

namespace CultuurNet\UDB3\Variations\ReadModel\Search;

use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Variations\Model\Events\OfferVariationCreated;
use CultuurNet\UDB3\Variations\Model\Events\OfferVariationDeleted;

class Projector implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

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

    protected function applyOfferVariationCreated(OfferVariationCreated $eventVariationCreated)
    {
        $this->repository->save(
            $eventVariationCreated->getId(),
            $eventVariationCreated->getEventUrl(),
            $eventVariationCreated->getOwnerId(),
            $eventVariationCreated->getPurpose(),
            $eventVariationCreated->getOfferType()
        );
    }

    protected function applyOfferVariationDeleted(OfferVariationDeleted $eventVariationDeleted)
    {
        $this->repository->remove($eventVariationDeleted->getId());
    }
}
