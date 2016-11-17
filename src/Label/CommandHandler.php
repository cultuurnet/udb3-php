<?php

namespace CultuurNet\UDB3\Label;

use Broadway\Repository\RepositoryInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use CultuurNet\UDB3\CommandHandling\Udb3CommandHandler as AbstractCommandHandler;
use CultuurNet\UDB3\EventSourcing\DBAL\UniqueConstraintException;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\Commands\Create;
use CultuurNet\UDB3\Label\Commands\CreateCopy;
use CultuurNet\UDB3\Label\Commands\MakeInvisible;
use CultuurNet\UDB3\Label\Commands\MakePrivate;
use CultuurNet\UDB3\Label\Commands\MakePublic;
use CultuurNet\UDB3\Label\Commands\MakeVisible;
use CultuurNet\UDB3\Label\Label as LabelAggregate;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Offer\Commands\AbstractAddLabel;
use CultuurNet\UDB3\Offer\Commands\AbstractSyncLabels;
use ValueObjects\Identity\UUID;

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
        if ($command instanceof AbstractAddLabel) {
            $this->handleAddLabel($command);
        } else if ($command instanceof AbstractSyncLabels) {
            $this->handleSyncLabels($command);
        } else {
            parent::handle($command);
        }
    }

    /**
     * @param Create $create
     */
    public function handleCreate(Create $create)
    {
        $label = LabelAggregate::create(
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
        $label = LabelAggregate::createCopy(
            $createCopy->getUuid(),
            $createCopy->getName(),
            $createCopy->getVisibility(),
            $createCopy->getPrivacy(),
            $createCopy->getParentUuid()
        );

        $this->save($label);
    }

    /**
     * @param AbstractSyncLabels $mergeLabels
     */
    public function handleSyncLabels(AbstractSyncLabels $mergeLabels)
    {
        $labelsArray = $mergeLabels->getLabelCollection()->asArray();

        array_walk(
            $labelsArray,
            function (Label $label) {
                $this->createLabel($label);
            }
        );
    }

    /**
     * @param AbstractAddLabel $addLabel
     */
    public function handleAddLabel(AbstractAddLabel $addLabel)
    {
        $this->createLabel($addLabel->getLabel());
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
     * @return LabelAggregate
     */
    private function load(UUID $uuid)
    {
        return $this->repository->load($uuid);
    }

    /**
     * @param LabelAggregate $label
     */
    private function save(LabelAggregate $label)
    {
        $this->repository->save($label);
    }

    /**
     * @param Label $label
     */
    private function createLabel(Label $label)
    {
        $label = LabelAggregate::create(
            new UUID($this->uuidGenerator->generate()),
            new LabelName((string)$label),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC()
        );
        try {
            $this->save($label);
        } catch (UniqueConstraintException $exception) {
        }
    }
}
