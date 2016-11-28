<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\LabelServiceInterface;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Organizer\Commands\AddLabel;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Organizer\Commands\DeleteOrganizer;
use CultuurNet\UDB3\Organizer\Commands\RemoveLabel;
use CultuurNet\UDB3\Title;
use ValueObjects\Web\Url;

class DefaultOrganizerEditingService implements OrganizerEditingServiceInterface
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
    protected $organizerRepository;

    /**
     * @var LabelServiceInterface
     */
    protected $labelService;

    /**
     * @param CommandBusInterface $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     * @param RepositoryInterface $organizerRepository
     * @param LabelServiceInterface $labelService
     */
    public function __construct(
        CommandBusInterface $commandBus,
        UuidGeneratorInterface $uuidGenerator,
        RepositoryInterface $organizerRepository,
        LabelServiceInterface $labelService
    ) {
        $this->commandBus = $commandBus;
        $this->uuidGenerator = $uuidGenerator;
        $this->organizerRepository = $organizerRepository;
        $this->labelService = $labelService;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Url $website, Title $title, Address $address = null, ContactPoint $contactPoint = null)
    {
        $id = $this->uuidGenerator->generate();

        $organizer = Organizer::create($id, $website, $title);

        if (!is_null($address)) {
            $organizer->updateAddress($address);
        }

        if (!is_null($contactPoint)) {
            $organizer->updateContactPoint($contactPoint);
        }

        $this->organizerRepository->save($organizer);

        return $id;
    }

    /**
     * @inheritdoc
     */
    public function addLabel($organizerId, Label $label)
    {
        $this->labelService->createLabelAggregateIfNew(
            new LabelName((string) $label),
            $label->isVisible()
        );

        return $this->commandBus->dispatch(
            new AddLabel($organizerId, $label)
        );
    }

    /**
     * @inheritdoc
     */
    public function removeLabel($organizerId, Label $label)
    {
        return $this->commandBus->dispatch(
            new RemoveLabel($organizerId, $label)
        );
    }

    /**
     * @inheritdoc
     */
    public function delete($id)
    {
        return $this->commandBus->dispatch(
            new DeleteOrganizer($id)
        );
    }
}
