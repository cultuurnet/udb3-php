<?php

namespace CultuurNet\UDB3\Role\Services;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use ValueObjects\Identity\UUID;

interface RoleReadingServiceInterface
{
    /**
     * @param UUID $uuid
     * @return JsonDocument
     */
    public function getPermissionsByRoleUuid(UUID $uuid);

    /**
     * @param UUID $uuid
     * @return JsonDocument
     */
    public function getLabelsByRoleUuid(UUID $uuid);
}
