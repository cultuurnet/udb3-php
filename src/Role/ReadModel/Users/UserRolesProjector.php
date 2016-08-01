<?php

namespace CultuurNet\UDB3\Role\ReadModel\Users;

use CultuurNet\UDB3\Event\ReadModel\DocumentGoneException;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\EventHandling\DelegateEventHandlingToSpecificMethodTrait;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Role\Events\UserAdded;
use CultuurNet\UDB3\Role\Events\UserRemoved;
use CultuurNet\UDB3\Role\ReadModel\RoleProjector;

class UserRolesProjector extends RoleProjector
{
    use DelegateEventHandlingToSpecificMethodTrait;

    /**
     * @var DocumentRepositoryInterface
     */
    private $roleDetailsDocumentRepository;

    /**
     * @param DocumentRepositoryInterface $userRolesDocumentRepository
     * @param DocumentRepositoryInterface $roleDetailsDocumentRepository
     */
    public function __construct(
        DocumentRepositoryInterface $userRolesDocumentRepository,
        DocumentRepositoryInterface $roleDetailsDocumentRepository
    ) {
        parent::__construct($userRolesDocumentRepository);
        $this->roleDetailsDocumentRepository = $roleDetailsDocumentRepository;
    }

    /**
     * @param UserAdded $userAdded
     */
    public function applyUserAdded(UserAdded $userAdded)
    {
        $userId = $userAdded->getUserId()->toNative();
        $roleId = $userAdded->getUuid()->toNative();

        try {
            $roleDetailsDocument = $this->roleDetailsDocumentRepository->get($roleId);
        } catch (DocumentGoneException $e) {
            return;
        }

        if (empty($roleDetailsDocument)) {
            return;
        }

        $roleDetails = $roleDetailsDocument->getBody();

        $document = $this->repository->get($userId);

        if (empty($document)) {
            $document = new JsonDocument(
                $userId,
                json_encode([])
            );
        }

        $roles = json_decode($document->getRawBody(), true);
        $roles[$roleId] = $roleDetails;

        $document = $document->withBody($roles);

        $this->repository->save($document);
    }

    /**
     * @param UserRemoved $userRemoved
     */
    public function applyUserRemoved(UserRemoved $userRemoved)
    {
        $userId = $userRemoved->getUserId()->toNative();
        $roleId = $userRemoved->getUuid()->toNative();

        $document = $this->repository->get($userId);
        $roles = json_decode($document->getRawBody(), true);
        unset($roles[$roleId]);

        $document = $document->withBody($roles);

        $this->repository->save($document);
    }
}
