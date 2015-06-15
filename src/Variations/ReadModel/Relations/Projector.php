<?php

namespace CultuurNet\UDB3\Variations\ReadModel\Relations;

use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Variations\Model\Events\EventVariationCreated;

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

    protected function applyEventVariationCreated(EventVariationCreated $eventVariationCreated)
    {
        $this->repository->storeRelations(
            $eventVariationCreated->getId(),
            $eventVariationCreated->getEventUrl(),
            $eventVariationCreated->getOwnerId(),
            $eventVariationCreated->getPurpose()
        );
    }
}
