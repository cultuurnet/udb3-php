<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\CommandHandling\CommandHandlerInterface;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Organizer\Commands\AddLabel;
use CultuurNet\UDB3\Organizer\Commands\DeleteOrganizer;

class OrganizerCommandHandler implements CommandHandlerInterface
{
    /**
     * @var RepositoryInterface
     */
    private $organizerRepository;

    /**
     * @var OrganizerRelationServiceInterface[]
     */
    private $organizerRelationServices;

    /**
     * @param RepositoryInterface $organizerRepository
     */
    public function __construct(
        RepositoryInterface $organizerRepository
    ) {
        $this->organizerRepository = $organizerRepository;
        $this->organizerRelationServices = [];
    }

    /**
     * @param OrganizerRelationServiceInterface $relationService
     * @return OrganizerCommandHandler
     */
    public function withOrganizerRelationService(OrganizerRelationServiceInterface $relationService)
    {
        $c = clone $this;
        $c->organizerRelationServices[] = $relationService;
        return $c;
    }

    /**
     * @return array
     */
    protected function getCommandHandlerMethods()
    {
        return [
            DeleteOrganizer::class => 'deleteOrganizer',
            AddLabel::class => 'addLabel'
        ];
    }

    /**
     * @param mixed $command
     */
    public function handle($command)
    {
        $class = get_class($command);
        $handlers = $this->getCommandHandlerMethods();

        if (isset($handlers[$class])) {
            $method = $handlers[$class];
            $this->{$method}($command);
        }
    }

    /**
     * @param AddLabel $addLabel
     */
    private function addLabel(AddLabel $addLabel)
    {
        $organizer = $this->loadOrganizer($addLabel->getOrganizerId());

        $organizer->addLabel($addLabel->getLabelId());

        $this->organizerRepository->save($organizer);
    }

    /**
     * @param DeleteOrganizer $deleteOrganizer
     */
    public function deleteOrganizer(DeleteOrganizer $deleteOrganizer)
    {
        $id = $deleteOrganizer->getOrganizerId();

        // First remove all relations to the given organizer.
        foreach ($this->organizerRelationServices as $relationService) {
            $relationService->deleteOrganizer($id);
        }

        // Delete the organizer itself.
        $organizer = $this->loadOrganizer($id);

        $organizer->delete();

        $this->organizerRepository->save($organizer);
    }

    /**
     * Makes it easier to type hint to Organizer.
     *
     * @param string $id
     * @return Organizer
     */
    protected function loadOrganizer($id)
    {
        return $this->organizerRepository->load($id);
    }
}
