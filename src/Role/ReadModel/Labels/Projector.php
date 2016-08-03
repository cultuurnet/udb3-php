<?php

namespace CultuurNet\UDB3\Role\ReadModel\Labels;

use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\Services\ReadServiceInterface;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Role\Events\LabelAdded;
use CultuurNet\UDB3\Role\Events\LabelRemoved;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleDeleted;
use CultuurNet\UDB3\Role\ReadModel\RoleProjector;
use ValueObjects\Identity\UUID;

class Projector extends RoleProjector
{
    /**
     * @var ReadServiceInterface
     */
    private $labelService;

    public function __construct(
        DocumentRepositoryInterface $repository,
        ReadServiceInterface $labelService
    ) {
        parent::__construct($repository);

        $this->labelService = $labelService;
    }

    /**
     * @param LabelAdded $labelAdded
     */
    public function applyLabelAdded(LabelAdded $labelAdded)
    {
        $document = $this->getDocument($labelAdded->getUuid());

        if ($document) {
            $labelDetails = $this->getLabelDetails($document);
            $label = $this->labelService->getByUuid($labelAdded->getLabelId());

            if ($label) {
                $labelDetails[] = $label;
                $document = $document->withBody($labelDetails);
                $this->repository->save($document);
            }
        }
    }

    /**
     * @param \CultuurNet\UDB3\Role\Events\LabelRemoved $labelRemoved
     */
    public function applyLabelRemoved(LabelRemoved $labelRemoved)
    {
        $document = $this->getDocument($labelRemoved->getUuid());

        if ($document) {
            $labelDetails = $this->getLabelDetails($document);
            $label = $this->labelService->getByUuid($labelRemoved->getLabelId());

            if ($label) {
                $labelDetails = array_diff($labelDetails, [$label]);
                $document = $document->withBody($labelDetails);
                $this->repository->save($document);
            }
        }
    }

    /**
     * @param \CultuurNet\UDB3\Role\Events\RoleCreated $roleCreated
     */
    public function applyRoleCreated(RoleCreated $roleCreated)
    {
        $document = $this->createNewDocument($roleCreated->getUuid());
        $this->repository->save($document);
    }

    /**
     * @param RoleDeleted $roleDeleted
     */
    public function applyRoleDeleted(RoleDeleted $roleDeleted)
    {
        $this->repository->remove($roleDeleted->getUuid());
    }

    /**
     * @param UUID $uuid
     * @return JsonDocument|null
     */
    private function getDocument(UUID $uuid)
    {
        $document = null;

        try {
            $document = $this->repository->get($uuid->toNative());
        } catch (DocumentGoneException $e) {
        }

        return $document;
    }

    /**
     * @param JsonDocument $document
     * @return Entity[]
     */
    private function getLabelDetails(JsonDocument $document)
    {
        return json_decode($document->getRawBody(), true);
    }

    /**
     * @param UUID $uuid
     * @return JsonDocument
     */
    private function createNewDocument(UUID $uuid)
    {
        $document = new JsonDocument(
            $uuid->toNative(),
            json_encode([])
        );
        return $document;
    }
}
