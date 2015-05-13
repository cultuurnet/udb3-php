<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\Actor;

use Broadway\Domain\AggregateRoot;
use Broadway\EventHandling\EventListenerInterface;
use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Cdb\ActorItemFactory;
use CultuurNet\UDB3\Cdb\CdbXmlContainerInterface;
use CultuurNet\UDB3\Cdb\UpdateableWithCdbXmlInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\UDB2\Actor\Events\ActorCreatedEnrichedWithCdbXml;
use CultuurNet\UDB3\UDB2\Actor\Events\ActorUpdatedEnrichedWithCdbXml;

/**
 * Applies incoming UDB2 actor events enriched with cdb xml on UDB3 organizers.
 *
 * Wether the UDB2 actor event should be processed is defined by an
 * implementation of ActorSpecificationInterface.
 *
 * Instantiation of new entities is delegated to an implementation of
 * ActorFactoryInterface.
 *
 * Entities targeted by the ActorEventApplier need to implement
 * UpdateableWithCdbXmlInterface.
 */
class ActorEventApplier implements EventListenerInterface
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var ActorSpecificationInterface
     */
    protected $actorSpecification;

    /**
     * @var ActorFactoryInterface
     */
    protected $actorFactory;

    /**
     * @param RepositoryInterface $repository
     * @param ActorFactoryInterface $actorFactory
     * @param ActorSpecificationInterface $actorSpecification
     */
    public function __construct(
        RepositoryInterface $repository,
        ActorFactoryInterface $actorFactory,
        ActorSpecificationInterface $actorSpecification
    ) {
        $this->repository = $repository;

        $this->actorSpecification = $actorSpecification;

        $this->actorFactory = $actorFactory;
    }

    /**
     * @param ActorCreatedEnrichedWithCdbXml $actorCreated
     */
    private function applyActorCreated(
        ActorCreatedEnrichedWithCdbXml $actorCreated
    ) {
        if (!$this->isSatisfiedBy($actorCreated)) {
            return;
        }

        $this->createWithUpdateFallback(
            $actorCreated->getActorId(),
            $actorCreated
        );
    }

    /**
     * @param ActorUpdatedEnrichedWithCdbXml $actorUpdated
     */
    private function applyActorUpdated(
        ActorUpdatedEnrichedWithCdbXml $actorUpdated
    ) {
        if (!$this->isSatisfiedBy($actorUpdated)) {
            return;
        }

        $this->updateWithCreateFallback(
            $actorUpdated->getActorId(),
            $actorUpdated
        );
    }

    /**
     * @param CdbXmlContainerInterface $actorCdbXml
     * @return bool
     */
    private function isSatisfiedBy(CdbXmlContainerInterface $actorCdbXml) {
        $actor = ActorItemFactory::createActorFromCdbXml(
            $actorCdbXml->getCdbXmlNamespaceUri(),
            $actorCdbXml->getCdbXml()
        );

        return $this->actorSpecification->isSatisfiedBy($actor);
    }

    /**
     * @param $entityId
     * @param CdbXmlContainerInterface $cdbXml
     */
    private function updateWithCreateFallback(
        $entityId,
        CdbXmlContainerInterface $cdbXml
    ) {
        try {
            $this->update($entityId, $cdbXml);
        }
        catch (AggregateNotFoundException $e) {
            $this->create($entityId, $cdbXml);
        }
    }

    /**
     * @param $entityId
     * @param CdbXmlContainerInterface $cdbXml
     */
    private function createWithUpdateFallback(
        $entityId,
        CdbXmlContainerInterface $cdbXml
    ) {
        try {
            $this->create($entityId, $cdbXml);
        } catch (\Exception $e) {
            $this->update($entityId, $cdbXml);
        }
    }

    /**
     * @param $entityId
     * @param CdbXmlContainerInterface $cdbXml
     */
    private function update(
        $entityId,
        CdbXmlContainerInterface $cdbXml
    ) {
        /** @var UpdateableWithCdbXmlInterface|AggregateRoot $entity */
        $entity = $this->repository->load($entityId);

        $entity->updateWithCdbXml(
            $cdbXml->getCdbXml(),
            $cdbXml->getCdbXmlNamespaceUri()
        );

        $this->repository->add($entity);
    }

    /**
     * @param string $id
     * @param CdbXmlContainerInterface $cdbXml
     */
    private function create(
        $id,
        CdbXmlContainerInterface $cdbXml
    ) {
        $entity = $this->actorFactory->createFromCdbXml(
            $id,
            $cdbXml->getCdbXml(),
            $cdbXml->getCdbXmlNamespaceUri()
        );

        $this->repository->add($entity);
    }
}
