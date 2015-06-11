<?php

namespace CultuurNet\UDB3\Variations;

use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Event\Event;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Variations\Command\EditDescription;
use CultuurNet\UDB3\Variations\Model\Events\EventVariationCreated;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;

class DefaultEventVariationService implements EventVariationServiceInterface
{
    /**
     * @var RepositoryInterface
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
        RepositoryInterface $eventVariationRepository,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->eventVariationRepository = $eventVariationRepository;
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function editDescription($eventId, $editorId, Purpose $purpose, $description)
    {
        $this->guardEventId($eventId);
        $personalEventVariation = $this->eventVariationService
          ->getPersonalEventVariation($eventId, $editorId);

        return $this->commandBus->dispatch(
            new EditDescription(
                $personalEventVariation->getAggregateRootId(),
                $editorId,
                new Purpose('personal'),
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
        new EventVariationCreated($eventVariationId, $originalEventId, $ownerId, new Purpose('personal'));

        // TODO: return an event variation...
    }
}
