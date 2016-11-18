<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository;

use CultuurNet\UDB3\Label\ValueObjects\LabelName;

interface ReadRepositoryInterface
{
    /**
     * @param LabelName $labelName
     * @return \Generator|LabelRelation[]
     */
    public function getLabelRelations(LabelName $labelName);
}
