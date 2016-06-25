<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository;

use CultuurNet\UDB3\Offer\OfferType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

interface WriteRepositoryInterface
{
    /**
     * @param UUID $uuid
     * @param OfferType $relationType
     * @param StringLiteral $relationId
     * @param StringLiteral $labelName
     */
    public function save(
        UUID $uuid,
        StringLiteral $labelName,
        OfferType $relationType,
        StringLiteral $relationId
    );

    /**
     * @param UUID $uuid
     * @param StringLiteral $relationId
     */
    public function deleteByUuidAndRelationId(
        UUID $uuid,
        StringLiteral $relationId
    );
}
