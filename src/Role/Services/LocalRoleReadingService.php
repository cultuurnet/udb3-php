<?php

namespace CultuurNet\UDB3\Role\Services;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\LocalEntityService;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class LocalRoleReadingService extends LocalEntityService implements RoleReadingServiceInterface
{
    /**
     * @var DocumentRepositoryInterface
     */
    private $rolePermissionsReadRepository;

    /**
     * @var DocumentRepositoryInterface
     */
    private $roleUsersReadRepository;

    /**
     * @var DocumentRepositoryInterface
     */
    private $userRolesReadRepository;

    /**
     * ReadRoleRestController constructor.
     * @param DocumentRepositoryInterface $roleReadRepository
     * @param RepositoryInterface $roleWriteRepository
     * @param IriGeneratorInterface $iriGenerator
     * @param DocumentRepositoryInterface $rolePermissionsReadRepository
     * @param DocumentRepositoryInterface $roleUsersReadRepository
     * @param DocumentRepositoryInterface $userRolesReadRepository
     */
    public function __construct(
        DocumentRepositoryInterface $roleReadRepository,
        RepositoryInterface $roleWriteRepository,
        IriGeneratorInterface $iriGenerator,
        DocumentRepositoryInterface $rolePermissionsReadRepository,
        DocumentRepositoryInterface $roleUsersReadRepository,
        DocumentRepositoryInterface $userRolesReadRepository
    ) {
        parent::__construct(
            $roleReadRepository,
            $roleWriteRepository,
            $iriGenerator
        );

        $this->rolePermissionsReadRepository = $rolePermissionsReadRepository;
        $this->roleUsersReadRepository = $roleUsersReadRepository;
        $this->userRolesReadRepository = $userRolesReadRepository;
    }

    /**
     * @param UUID $uuid
     * @return mixed
     */
    public function getPermissionsByRoleUuid(UUID $uuid)
    {
        return $this->rolePermissionsReadRepository->get($uuid->toNative());
    }

    /**
     * @param UUID $uuid
     * @return JsonDocument
     */
    public function getUsersByRoleUuid(UUID $uuid)
    {
        return $this->roleUsersReadRepository->get($uuid->toNative());
    }

    /**
     * @param StringLiteral $userId
     * @return JsonDocument
     */
    public function getRolesByUserId(StringLiteral $userId)
    {
        return $this->userRolesReadRepository->get($userId->toNative());
    }
}
