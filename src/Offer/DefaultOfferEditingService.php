<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Commands\OfferCommandFactoryInterface;
use ValueObjects\String\String;

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
     * @var DocumentRepositoryInterface
     */
    protected $readRepository;

    /**
     * @var OfferCommandFactoryInterface
     */
    protected $commandFactory;

    /**
     * @param CommandBusInterface $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     * @param DocumentRepositoryInterface $readRepository
     * @param OfferCommandFactoryInterface $commandFactory
     */
    public function __construct(
        CommandBusInterface $commandBus,
        UuidGeneratorInterface $uuidGenerator,
        DocumentRepositoryInterface $readRepository,
        OfferCommandFactoryInterface $commandFactory
    ) {
        $this->commandBus = $commandBus;
        $this->uuidGenerator = $uuidGenerator;
        $this->readRepository = $readRepository;
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

        return $this->commandBus->dispatch(
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

        return $this->commandBus->dispatch(
            $this->commandFactory->createDeleteLabelCommand(
                $id,
                $label
            )
        );
    }

    /**
     * @param $id
     * @param Language $language
     * @param String $title
     * @return string
     */
    public function translateTitle($id, Language $language, String $title)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createTranslateTitleCommand(
                $id,
                $language,
                $title
            )
        );
    }

    /**
     * @param $id
     * @param Language $language
     * @param String $description
     * @return string
     */
    public function translateDescription($id, Language $language, String $description)
    {
        $this->guardId($id);

        return $this->commandBus->dispatch(
            $this->commandFactory->createTranslateDescriptionCommand(
                $id,
                $language,
                $description
            )
        );
    }

    /**
     * @param string $id
     */
    public function guardId($id)
    {
        $this->readRepository->get($id);
    }
}
