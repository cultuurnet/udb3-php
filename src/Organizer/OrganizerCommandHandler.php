<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\CommandHandling\CommandHandlerInterface;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Organizer\Commands\DeleteOrganizer;

class OrganizerCommandHandler implements CommandHandlerInterface
{
    /**
     * @var RepositoryInterface
     */
    private $organizerRepository;

    /**
     * @param RepositoryInterface $organizerRepository
     */
    public function __construct(
        RepositoryInterface $organizerRepository
    ) {
        $this->organizerRepository = $organizerRepository;
    }

    /**
     * @return array
     */
    protected function getCommandHandlerMethods()
    {
        return [
            DeleteOrganizer::class => 'deleteOrganizer',
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
     * @param DeleteOrganizer $deleteOrganizer
     */
    public function deleteOrganizer(DeleteOrganizer $deleteOrganizer)
    {
        $organizer = $this->loadOrganizer(
            $deleteOrganizer->getOrganizerId()
        );

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
