<?php

namespace CultuurNet\UDB3\Label;

use Broadway\CommandHandling\CommandHandler as AbstractCommandHandler;
use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\EventSourcing\DBAL\UniqueConstraintException;
use CultuurNet\UDB3\Label\Commands\Create;
use CultuurNet\UDB3\Label\Commands\CreateCopy;
use CultuurNet\UDB3\Label\Commands\MakeInvisible;
use CultuurNet\UDB3\Label\Commands\MakePrivate;
use CultuurNet\UDB3\Label\Commands\MakePublic;
use CultuurNet\UDB3\Label\Commands\MakeVisible;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Offer\Commands\AbstractAddLabel;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class CommandHandler extends AbstractCommandHandler
{
    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * CommandHandler constructor.
     * @param RepositoryInterface $repository
     * @param UuidGeneratorInterface $uuidGenerator
     */
    public function __construct(
        RepositoryInterface $repository,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->repository = $repository;
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * @inheritdoc
     */
    public function handle($command)
    {
        if (is_a($command, AbstractAddLabel::class)) {
            $this->handleAddLabel($command);
        } else {
            parent::handle($command);
        }
    }

    /**
     * @param Create $create
     */
    public function handleCreate(Create $create)
    {
        $label = Label::create(
            $create->getUuid(),
            $create->getName(),
            $create->getVisibility(),
            $create->getPrivacy()
        );

        $this->save($label);
    }

    /**
     * @param CreateCopy $createCopy
     */
    public function handleCreateCopy(CreateCopy $createCopy)
    {
        $label = Label::createCopy(
            $createCopy->getUuid(),
            $createCopy->getName(),
            $createCopy->getVisibility(),
            $createCopy->getPrivacy(),
            $createCopy->getParentUuid()
        );

        $this->save($label);
    }

    public function handleAddLabel(AbstractAddLabel $addLabel)
    {
        $this->createLabel($addLabel);
    }

    /**
     * @param MakeVisible $makeVisible
     */
    public function handleMakeVisible(MakeVisible $makeVisible)
    {
        $label = $this->load($makeVisible->getUuid());

        $label->makeVisible();

        $this->save($label);
    }

    /**
     * @param MakeInvisible $makeInvisible
     */
    public function handleMakeInvisible(MakeInvisible $makeInvisible)
    {
        $label = $this->load($makeInvisible->getUuid());

        $label->makeInvisible();

        $this->save($label);
    }

    /**
     * @param MakePublic $makePublic
     */
    public function handleMakePublic(MakePublic $makePublic)
    {
        $label = $this->load($makePublic->getUuid());

        $label->makePublic();

        $this->save($label);
    }

    /**
     * @param MakePrivate $makePrivate
     */
    public function handleMakePrivate(MakePrivate $makePrivate)
    {
        $label = $this->load($makePrivate->getUuid());

        $label->makePrivate();

        $this->save($label);
    }

    /**
     * @param UUID $uuid
     * @return Label
     */
    private function load(UUID $uuid)
    {
        return $this->repository->load($uuid);
    }

    /**
     * @param Label $label
     */
    private function save(Label $label)
    {
        $this->repository->save($label);
    }

    /**
     * @param AbstractAddLabel $addLabel
     */
    private function createLabel(AbstractAddLabel $addLabel)
    {
        $label = Label::create(
            new UUID($this->uuidGenerator->generate()),
            new StringLiteral((string)$addLabel->getLabel()),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC()
        );
        try {
            $this->save($label);
        } catch (UniqueConstraintException $exception) {
        }
    }
}
