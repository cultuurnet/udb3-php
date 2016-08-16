<?php

namespace CultuurNet\UDB3\Role\ReadModel\Constraints;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

interface UserConstraintsWriteRepositoryInterface
{
    /**
     * @param UUID $roleId
     */
    public function removeRole(UUID $roleId);

    /**
     * @param UUID $roleId
     * @param StringLiteral $constraint
     */
    public function insertRole(UUID $roleId, StringLiteral $constraint);

    /**
     * @param UUID $roleId
     * @param StringLiteral $constraint
     */
    public function updateRole(UUID $roleId, StringLiteral $constraint);
}
