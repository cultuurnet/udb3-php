<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository;

use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use ValueObjects\StringLiteral\StringLiteral;

interface ReadRepositoryInterface
{
    /**
     * @param LabelName $labelName
     * @return \Generator|LabelRelation[]
     */
    public function getLabelRelations(LabelName $labelName);

    /**
     * @param RelationType $relationType
     * @param StringLiteral $relationId
     * @return LabelRelation[]
     */
    public function getLabelRelationsForItem(
        RelationType $relationType,
        StringLiteral $relationId
    );
}
