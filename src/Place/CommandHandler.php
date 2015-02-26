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

    public function handleUpdateDescription(UpdateDescription $updateDescription)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($updateDescription->getId());

        $place->updateDescription(
            $updateDescription->getDescription()
        );

        $this->placeRepository->add($place);

    }

    public function handleUpdateTypicalAgeRange(UpdateTypicalAgeRange $typicalAgeRange)
    {

        /** @var Place $place */
        $place = $this->placeRepository->load($typicalAgeRange->getId());

        $place->updateTypicalAgeRange(
            $typicalAgeRange->getTypicalAgeRange()
        );

        $this->placeRepository->add($place);

    }

}
