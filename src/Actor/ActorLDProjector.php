<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Actor\ActorLDProjector.
 */

namespace CultuurNet\UDB3\Actor;

use Broadway\EventHandling\EventBusInterface;
use Broadway\EventHandling\EventListenerInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;

abstract class ActorLDProjector implements EventListenerInterface
{

    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var DocumentRepositoryInterface
     */
    protected $repository;

    /**
     * @var IriGeneratorInterface
     */
    protected $iriGenerator;

    /**
     * @var EventBusInterface
     */
    protected $eventBus;

    /**
     * @param DocumentRepositoryInterface $repository
     * @param IriGeneratorInterface $iriGenerator
     */
    public function __construct(
        DocumentRepositoryInterface $repository,
        IriGeneratorInterface $iriGenerator,
        EventBusInterface $eventBus
    ) {
        $this->repository = $repository;
        $this->iriGenerator = $iriGenerator;
        $this->eventBus = $eventBus;
    }

    /**
     * @param ActorCreated $actorCreated
     */
    protected function applyActorCreated(ActorCreated $actorCreated)
    {
        // @todo This just creates an empty event. Should we do anything here?
    }

    /**
     * @param ActorEvent $actor
     * @return JsonDocument
     */
    protected function loadDocumentFromRepository(ActorEvent $actor)
    {
        $document = $this->repository->get($actor->getActorId());

        if (!$document) {
            return $this->newDocument($actor->getActorId());
        }

        return $document;
    }

    /**
     * Returns an iri.
     *
     * @param string $id
     *   The id.
     *
     * @return string
     */
    public function iri($id)
    {
        return $this->iriGenerator->iri($id);
    }

    /**
     * @param string $id
     * @return JsonDocument
     */
    abstract protected function newDocument($id);
}
