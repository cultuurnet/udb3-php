<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository;

use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

interface WriteRepositoryInterface
{
    /**
     * @param UUID $uuid
     * @param RelationType $relationType
     * @param StringLiteral $relationId
     */
    public function save(
        UUID $uuid,
        RelationType $relationType,
        StringLiteral $relationId
    );

    /**
     * @param UUID $uuid
     */
    public function deleteByUuid(UUID $uuid);
}
