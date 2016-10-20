<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Organizer\Commands\AddLabel;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Organizer\Commands\DeleteOrganizer;
use CultuurNet\UDB3\Organizer\Commands\RemoveLabel;
use CultuurNet\UDB3\Title;
use ValueObjects\Identity\UUID;
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
     * @param CommandBusInterface $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     * @param RepositoryInterface $organizerRepository
     */
    public function __construct(
        CommandBusInterface $commandBus,
        UuidGeneratorInterface $uuidGenerator,
        RepositoryInterface $organizerRepository
    ) {
        $this->commandBus = $commandBus;
        $this->uuidGenerator = $uuidGenerator;
        $this->organizerRepository = $organizerRepository;
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
    public function addLabel($organizerId, UUID $labelId)
    {
        return $this->commandBus->dispatch(
            new AddLabel($organizerId, $labelId)
        );
    }

    /**
     * @inheritdoc
     */
    public function removeLabel($organizerId, UUID $labelId)
    {
        return $this->commandBus->dispatch(
            new RemoveLabel($organizerId, $labelId)
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
