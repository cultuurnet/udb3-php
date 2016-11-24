<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\CommandHandling\CommandHandlerInterface;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Organizer\Commands\AddLabel;
use CultuurNet\UDB3\Organizer\Commands\DeleteOrganizer;
use CultuurNet\UDB3\Organizer\Commands\RemoveLabel;
use ValueObjects\String\String as StringLiteral;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;

class OrganizerCommandHandler implements CommandHandlerInterface
{
    /**
     * @var RepositoryInterface
     */
    private $organizerRepository;

    /**
     * @var ReadRepositoryInterface
     */
    private $labelRepository;

    /**
     * @var OrganizerRelationServiceInterface[]
     */
    private $organizerRelationServices;

    /**
     * @param RepositoryInterface $organizerRepository
     * @param ReadRepositoryInterface $labelRepository
     */
    public function __construct(
        RepositoryInterface $organizerRepository,
        ReadRepositoryInterface $labelRepository
    ) {
        $this->organizerRepository = $organizerRepository;
        $this->labelRepository = $labelRepository;
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
            AddLabel::class => 'addLabel',
            RemoveLabel::class => 'removeLabel'
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
    protected function addLabel(AddLabel $addLabel)
    {
        $organizer = $this->loadOrganizer($addLabel->getOrganizerId());

        $labelName = new StringLiteral((string) $addLabel->getLabel());
        $label = $this->labelRepository->getByName($labelName);

        $organizer->addLabel(new Label(
            $label->getName()->toNative(),
            $label->getVisibility() === Visibility::VISIBLE()
        ));

        $this->organizerRepository->save($organizer);
    }

    /**
     * @param RemoveLabel $removeLabel
     */
    protected function removeLabel(RemoveLabel $removeLabel)
    {
        $organizer = $this->loadOrganizer($removeLabel->getOrganizerId());

        $organizer->removeLabel($removeLabel->getLabel());

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
