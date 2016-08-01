<?php

namespace CultuurNet\UDB3\Role\ReadModel\Users;

use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Role\Events\RoleCreated;
use CultuurNet\UDB3\Role\Events\RoleDeleted;
use CultuurNet\UDB3\Role\Events\UserAdded;
use CultuurNet\UDB3\Role\Events\UserRemoved;
use CultuurNet\UDB3\Role\ReadModel\RoleProjector;
use CultuurNet\UDB3\User\UserIdentityDetails;
use CultuurNet\UDB3\User\UserIdentityResolverInterface;
use ValueObjects\Identity\UUID;

class RoleUsersProjector extends RoleProjector
{
    /**
     * @var UserIdentityResolverInterface
     */
    private $userIdentityResolver;

    public function __construct(
        DocumentRepositoryInterface $repository,
        UserIdentityResolverInterface $userIdentityResolver
    ) {
        parent::__construct($repository);

        $this->userIdentityResolver = $userIdentityResolver;
    }

    /**
     * @param UserAdded $userAdded
     */
    public function applyUserAdded(UserAdded $userAdded)
    {
        $document = $this->loadDocumentFromRepositoryByUuid(
            $userAdded->getUuid()
        );

        $userIdentityDetails = $this->getUserIdentityDetails($document);
        $userIdentityDetail = $this->userIdentityResolver->getUserById(
            $userAdded->getUuid()
        );

        $userIdentityDetails[$userAdded->getUserId()->toNative()] = $userIdentityDetail;

        $this->repository->save($document);
    }

    /**
     * @param UserRemoved $userRemoved
     */
    public function applyUserRemoved(UserRemoved $userRemoved)
    {
        $document = $this->loadDocumentFromRepositoryByUuid(
            $userRemoved->getUuid()
        );

        $userIdentityDetails = $this->getUserIdentityDetails($document);
        unset($userIdentityDetails[$userRemoved->getUserId()->toNative()]);

        if (count($userIdentityDetails) === 0) {
            $this->repository->remove($userRemoved->getUuid());
        } else {
            $this->repository->save($document);
        }
    }

    /**
     * @param RoleCreated $roleCreated
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
     * @param JsonDocument $document
     * @return UserIdentityDetails[]
     */
    private function getUserIdentityDetails(JsonDocument $document)
    {
        $body = $document->getBody();

        return $body->userIdentityDetails;
    }

    /**
     * @param UUID $uuid
     * @return JsonDocument
     */
    private function createNewDocument(UUID $uuid)
    {
        $document = new JsonDocument($uuid->toNative());
        $body = $document->getBody();
        $body->userIdentityDetails = [];

        return $document;
    }
}
