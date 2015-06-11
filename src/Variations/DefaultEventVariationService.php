<?php

namespace CultuurNet\UDB3\Variations;

use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Variations\Model\EventVariation;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;

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

    /**
     * @param RepositoryInterface $eventVariationRepository
     * @param UuidGeneratorInterface $uuidGenerator
     */
    public function __construct(
        RepositoryInterface $eventVariationRepository,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->eventVariationRepository = $eventVariationRepository;
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * @inheritdoc
     */
    public function createEventVariation(
        Url $eventUrl,
        OwnerId $ownerId,
        Purpose $purpose,
        Description $description
    ) {
        $variation = EventVariation::create(
            new Id($this->uuidGenerator->generate()),
            $eventUrl,
            $ownerId,
            $purpose,
            $description
        );

        $this->eventVariationRepository->save($variation);

        return $variation;
    }
}
