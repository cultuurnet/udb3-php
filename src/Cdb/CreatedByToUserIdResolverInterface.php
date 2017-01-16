<?php
/**
 * @file
 */
namespace CultuurNet\UDB3\Cdb;

use ValueObjects\StringLiteral\StringLiteral;

interface CreatedByToUserIdResolverInterface
{
    /**
     * @param StringLiteral $createdByIdentifier
     * @return String
     */
    public function resolveCreatedByToUserId(StringLiteral $createdByIdentifier);
}
