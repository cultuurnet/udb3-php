<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;

class DefaultOfferEditingService implements OfferEditingServiceInterface
{
    /**
     * @var CommandBusInterface
     */
    protected $commandBus;

    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * @var RepositoryInterface
     */
    protected $offerRepository;

    /**
     * @var OfferCommandFactoryInterface
     */
    protected $commandFactory;

    /**
     * @param CommandBusInterface $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     * @param RepositoryInterface $offerRepository
     * @param OfferCommandFactoryInterface $commandFactory
     */
    public function __construct(
        CommandBusInterface $commandBus,
        UuidGeneratorInterface $uuidGenerator,
        RepositoryInterface $offerRepository,
        OfferCommandFactoryInterface $commandFactory
    ) {
        $this->commandBus = $commandBus;
        $this->uuidGenerator = $uuidGenerator;
        $this->offerRepository = $offerRepository;
        $this->commandFactory = $commandFactory;
    }

    /**
     * @param $id
     * @param Label $label
     * @return string
     */
    public function addLabel($id, Label $label)
    {
        $this->guardId($id);

        $this->commandBus->dispatch(
            $this->commandFactory->createAddLabelCommand(
                $id,
                $label
            )
        );
    }

    /**
     * @param $id
     * @param Label $label
     * @return string
     */
    public function deleteLabel($id, Label $label)
    {
        $this->guardId($id);

        $this->commandBus->dispatch(
            $this->commandFactory->createDeleteLabelCommand(
                $id,
                $label
            )
        );
    }

    /**
     * @param string $id
     */
    private function guardId($id)
    {
        $this->offerRepository->load($id);
    }
}
