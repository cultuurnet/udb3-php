<?php

namespace CultuurNet\UDB3\Variations;

use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\EventServiceInterface;
use CultuurNet\UDB3\Variations\Model\OfferVariation;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;

class DefaultEventVariationService implements OfferVariationServiceInterface
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
     * {@inheritdoc}
     */
    public function createEventVariation(
        Url $eventUrl,
        OwnerId $ownerId,
        Purpose $purpose,
        Description $description
    ) {
        $variation = OfferVariation::create(
            new Id($this->uuidGenerator->generate()),
            $eventUrl,
            $ownerId,
            $purpose,
            $description
        );

        $this->eventVariationRepository->save($variation);

        return $variation;
    }

    /**
     * {@inheritdoc}
     */
    public function editDescription(Id $id, Description $description)
    {
        /** @var OfferVariation $variation */
        $variation = $this->eventVariationRepository->load((string) $id);

        $variation->editDescription($description);

        $this->eventVariationRepository->save($variation);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteEventVariation(Id $id)
    {
        /** @var OfferVariation $variation */
        $variation = $this->eventVariationRepository->load((string) $id);

        $variation->markDeleted();

        $this->eventVariationRepository->save($variation);
    }
}
