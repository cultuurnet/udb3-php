<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository;

use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use ValueObjects\String\String as StringLiteral;

interface WriteRepositoryInterface
{
    /**
     * @param LabelName $labelName
     * @param RelationType $relationType
     * @param StringLiteral $relationId
     */
    public function save(
        LabelName $labelName,
        RelationType $relationType,
        StringLiteral $relationId
    );

    /**
     * @param LabelName $labelName
     * @param StringLiteral $relationId
     */
    public function deleteByLabelNameAndRelationId(
        LabelName $labelName,
        StringLiteral $relationId
    );
}
