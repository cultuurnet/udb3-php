<?php


namespace CultuurNet\UDB3\Place;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Place\Place;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class CommandHandler extends Udb3CommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var RepositoryInterface
     */
    protected $placeRepository;

    public function __construct(
        RepositoryInterface $placeRepository
    ) {
        $this->placeRepository = $placeRepository;
    }

    /**
     * Handle the update of description on a place.
     */
    public function handleUpdateDescription(UpdateDescription $updateDescription)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($updateDescription->getId());

        $place->updateDescription(
            $updateDescription->getDescription()
        );

        $this->placeRepository->add($place);

    }

    /**
     * Handle the update of typical age range on a place.
     */
    public function handleUpdateTypicalAgeRange(UpdateTypicalAgeRange $typicalAgeRange)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($typicalAgeRange->getId());

        $place->updateTypicalAgeRange(
            $typicalAgeRange->getTypicalAgeRange()
        );

        $this->placeRepository->add($place);

    }

    /**
     * Handle an update command to update organizer.
     */
    public function handleUpdateOrganizer(UpdateOrganizer $updateOrganizer)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($updateOrganizer->getId());

        $place->updateOrganizer(
            $updateOrganizer->getOrganizerId()
        );

        $this->placeRepository->add($place);

    }
}
