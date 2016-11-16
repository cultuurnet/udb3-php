<?php

namespace CultuurNet\UDB3\Label;

use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use ValueObjects\Identity\UUID;

interface LabelServiceInterface
{
    /**
     * @param LabelName $labelName
     *
     * @return UUID|null
     *   UUID of the newly created aggregate label, or null if no new label
     *   aggregate was created.
     */
    public function createLabelAggregateIfNew(LabelName $labelName);
}
