<?php

namespace CultuurNet\UDB3\Variations;

use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\UDB2\EventNotFoundException;

class DefaultEventVariationService implements EventVariationServiceInterface
{
    /**
     * @var EventVariationRepositoryInterface
     */
    protected $eventVariationRepository;

    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    public function __construct(
        EventVariationRepositoryInterface $eventVariationRepository,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->eventVariationRepository = $eventVariationRepository;
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function editDescription($eventId, $editorId, EditPurpose $purpose, $description)
    {
        $this->guardEventId($eventId);
        $personalEventVariation = $this->eventVariationService
          ->getPersonalEventVariation($eventId, $editorId);

        return $this->commandBus->dispatch(
          new EditDescription(
            $personalEventVariation->getAggregateRootId(),
            $editorId,
            new EditPurpose('personal'),
            $description
          )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPersonalEventVariation($originalEventId, $ownerId)
    {
        try {
            $eventVariation = $this->eventVariationRepository
              ->getPersonalVariation($originalEventId);
        } catch (EventVariationNotFoundException $e) {
            $eventVariation = $this->createPersonalEventVariation($originalEventId, $ownerId);
        }

        return $eventVariation;
    }

    /**
     * @param string $originalEventId
     * @param string $ownerId
     *
     * @return Event
     */
    protected function createPersonalEventVariation($originalEventId, $ownerId)
    {
        $originalEvent = $this->eventService->getEvent($originalEventId);
        $eventVariationId = $this->uuidGenerator->generate();
        new EventVariationCreated($eventVariationId, $originalEventId, $ownerId);

        // TODO: return an event variation...
    }
}
