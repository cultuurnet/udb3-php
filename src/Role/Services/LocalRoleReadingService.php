<?php

namespace CultuurNet\UDB3\Role\Services;

use CultuurNet\UDB3\Event\ReadModel\DocumentRepositoryInterface;
use ValueObjects\Identity\UUID;

class LocalRoleReadingService implements RoleReadingServiceInterface
{
    /**
     * @var DocumentRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var DocumentRepositoryInterface
     */
    private $rolePermissionsRepository;

    /**
     * ReadRoleRestController constructor.
     * @param DocumentRepositoryInterface $roleRepository
     * @param DocumentRepositoryInterface $rolePermissionsRepository
     */
    public function __construct(
        DocumentRepositoryInterface $roleRepository,
        DocumentRepositoryInterface $rolePermissionsRepository
    ) {
        $this->roleRepository = $roleRepository;
        $this->rolePermissionsRepository = $rolePermissionsRepository;
    }
    
    /**
     * @param UUID $uuid
     * @return mixed
     */
    public function getByUuid(UUID $uuid)
    {
        return $this->roleRepository->get($uuid->toNative());
    }

    /**
     * @param UUID $uuid
     * @return mixed
     */
    public function getPermissionsByRoleUuid(UUID $uuid)
    {
        return $this->rolePermissionsRepository->get($uuid->toNative());
    }
}
