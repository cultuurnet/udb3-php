<?php

namespace CultuurNet\UDB3\Label\ReadModels\JSON;

use CultuurNet\UDB3\Label\Events\CopyCreated;
use CultuurNet\UDB3\Label\Events\Created;
use CultuurNet\UDB3\Label\Events\MadeInvisible;
use CultuurNet\UDB3\Label\Events\MadePrivate;
use CultuurNet\UDB3\Label\Events\MadePublic;
use CultuurNet\UDB3\Label\Events\MadeVisible;
use CultuurNet\UDB3\Label\ReadModels\AbstractProjector;
use CultuurNet\UDB3\Label\ReadModels\Helper\LabelEventHelper;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\WriteRepositoryInterface;
use CultuurNet\UDB3\Offer\Events\AbstractLabelAdded;
use CultuurNet\UDB3\Offer\Events\AbstractLabelDeleted;

class Projector extends AbstractProjector
{
    /**
     * @var WriteRepositoryInterface
     */
    private $writeRepository;

    /**
     * @var ReadRepositoryInterface
     */
    private $readRepository;

    /**
     * @var LabelEventHelper
     */
    private $abstractLabelEventHelper;

    /**
     * Projector constructor.
     * @param WriteRepositoryInterface $writeRepository
     * @param ReadRepositoryInterface $readRepository
     * @param LabelEventHelper $abstractLabelEventHelper
     */
    public function __construct(
        WriteRepositoryInterface $writeRepository,
        ReadRepositoryInterface $readRepository,
        LabelEventHelper $abstractLabelEventHelper
    ) {
        $this->writeRepository = $writeRepository;
        $this->readRepository = $readRepository;
        $this->abstractLabelEventHelper = $abstractLabelEventHelper;
    }

    /**
     * @param Created $created
     */
    public function applyCreated(Created $created)
    {
        $entity = $this->readRepository->getByUuid($created->getUuid());

        if (is_null($entity)) {
            $this->writeRepository->save(
                $created->getUuid(),
                $created->getName(),
                $created->getVisibility(),
                $created->getPrivacy()
            );
        }
    }

    /**
     * @param CopyCreated $copyCreated
     */
    public function applyCopyCreated(CopyCreated $copyCreated)
    {
        $entity = $this->readRepository->getByUuid($copyCreated->getUuid());

        if (is_null($entity)) {
            $this->writeRepository->save(
                $copyCreated->getUuid(),
                $copyCreated->getName(),
                $copyCreated->getVisibility(),
                $copyCreated->getPrivacy(),
                $copyCreated->getParentUuid()
            );
        }
    }

    /**
     * @param MadeVisible $madeVisible
     */
    public function applyMadeVisible(MadeVisible $madeVisible)
    {
        $this->writeRepository->updateVisible($madeVisible->getUuid());
    }

    /**
     * @param MadeInvisible $madeInvisible
     */
    public function applyMadeInvisible(MadeInvisible $madeInvisible)
    {
        $this->writeRepository->updateInvisible($madeInvisible->getUuid());
    }

    /**
     * @param MadePublic $madePublic
     */
    public function applyMadePublic(MadePublic $madePublic)
    {
        $this->writeRepository->updatePublic($madePublic->getUuid());
    }

    /**
     * @param MadePrivate $madePrivate
     */
    public function applyMadePrivate(MadePrivate $madePrivate)
    {
        $this->writeRepository->updatePrivate($madePrivate->getUuid());
    }

    /**
     * @inheritdoc
     */
    public function applyLabelAdded(AbstractLabelAdded $labelAdded)
    {
        $uuid = $this->abstractLabelEventHelper->getUuid($labelAdded);

        if ($uuid) {
            $this->writeRepository->updateCountIncrement($uuid);
        }
    }

    /**
     * @inheritdoc
     */
    public function applyLabelDeleted(AbstractLabelDeleted $labelDeleted)
    {
        $uuid = $this->abstractLabelEventHelper->getUuid($labelDeleted);

        if ($uuid) {
            $this->writeRepository->updateCountDecrement($uuid);
        }
    }
}
