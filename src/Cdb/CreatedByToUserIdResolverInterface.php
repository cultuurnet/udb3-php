<?php
/**
 * @file
 */
namespace CultuurNet\UDB3\Cdb;

use ValueObjects\String\String;

interface CreatedByToUserIdResolverInterface
{
    /**
     * @param String $createdByIdentifier
     * @return String
     */
    public function resolveCreatedByToUserId(String $createdByIdentifier);
}
