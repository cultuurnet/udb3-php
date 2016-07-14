<?php

namespace CultuurNet\UDB3\Role\Services;

use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\LocalEntityService;
use ValueObjects\Identity\UUID;

class LocalRoleReadingService extends LocalEntityService implements RoleReadingServiceInterface
{
    /**
     * @var DocumentRepositoryInterface
     */
    private $rolePermissionsReadRepository;

    /**
     * ReadRoleRestController constructor.
     * @param DocumentRepositoryInterface $roleReadRepository
     * @param RepositoryInterface $roleWriteRepository
     * @param IriGeneratorInterface $iriGenerator
     * @param DocumentRepositoryInterface $rolePermissionsReadRepository
     */
    public function __construct(
        DocumentRepositoryInterface $roleReadRepository,
        RepositoryInterface $roleWriteRepository,
        IriGeneratorInterface $iriGenerator,
        DocumentRepositoryInterface $rolePermissionsReadRepository
    ) {
        parent::__construct(
            $roleReadRepository,
            $roleWriteRepository,
            $iriGenerator
        );

        $this->rolePermissionsReadRepository = $rolePermissionsReadRepository;
    }

    /**
     * @param UUID $uuid
     * @return mixed
     */
    public function getPermissionsByRoleUuid(UUID $uuid)
    {
        return $this->rolePermissionsReadRepository->get($uuid->toNative());
    }
}
