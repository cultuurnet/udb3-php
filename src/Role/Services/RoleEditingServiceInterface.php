<?php

namespace CultuurNet\UDB3\Role\Services;

use CultuurNet\UDB3\Role\ValueObjects\Permission;
use CultuurNet\UDB3\Role\ValueObjects\Query;
use CultuurNet\UDB3\ValueObject\SapiVersion;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

interface RoleEditingServiceInterface
{
    /**
     * Create a role.
     *
     * @param StringLiteral $name
     * @return string
     */
    public function create(StringLiteral $name);

    /**
     * Rename a role.
     *
     * @param UUID $uuid
     * @param StringLiteral $name
     * @return string
     */
    public function rename(UUID $uuid, StringLiteral $name);

    /**
     * Add a permission to a role.
     *
     * @param UUID $uuid
     * @param Permission $permission
     * @return string
     */
    public function addPermission(UUID $uuid, Permission $permission);

    /**
     * Remove a permission from a role.
     *
     * @param UUID $uuid
     * @param Permission $permission
     * @return string
     */
    public function removePermission(UUID $uuid, Permission $permission);

    /**
     * @param UUID $uuid
     * @param StringLiteral $userId
     * @return string
     */
    public function addUser(UUID $uuid, StringLiteral $userId);

    /**
     * @param UUID $uuid
     * @param StringLiteral $userId
     * @return string
     */
    public function removeUser(UUID $uuid, StringLiteral $userId);

    /**
     * @param UUID $uuid
     * @param SapiVersion $sapiVersion
     * @param Query $query
     * @return string
     */
    public function addConstraint(UUID $uuid, SapiVersion $sapiVersion, Query $query): string;

    /**
     * @param UUID $uuid
     * @param SapiVersion $sapiVersion
     * @param Query $query
     * @return string
     */
    public function updateConstraint(UUID $uuid, SapiVersion $sapiVersion, Query $query): string;

    /**
     * @param UUID $uuid
     * @param SapiVersion $sapiVersion
     * @return string
     */
    public function removeConstraint(UUID $uuid, SapiVersion $sapiVersion): string;

    /**
     * Add a label to a role.
     *
     * @param UUID $uuid
     * @param UUID $labelId
     * @return string
     */
    public function addLabel(UUID $uuid, UUID $labelId);

    /**
     * Remove a label from a role.
     *
     * @param UUID $uuid
     * @param UUID $labelId
     * @return string
     */
    public function removeLabel(UUID $uuid, UUID $labelId);

    /**
     * Deleting a role.
     *
     * @param UUID $uuid
     * @return string
     */
    public function delete(UUID $uuid);
}
