<?php

namespace CultuurNet\UDB3\Role\Services;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

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
    public function getUsersByRoleUuid(UUID $uuid);

    /**
     * @param StringLiteral $userId
     * @return JsonDocument
     */
    public function getRolesByUserId(StringLiteral $userId);

    /**
     * @param StringLiteral $userId
     * @return Permission[]
     */
    public function getPermissionsByUserId(StringLiteral $userId);
}
