<?php

namespace CultuurNet\UDB3\Label;

use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use CultuurNet\UDB3\LabelEventInterface;

interface LabelEventRelationTypeResolverInterface
{
    /**
     * @param LabelEventInterface $labelEvent
     * @return RelationType
     * @throws \InvalidArgumentException
     */
    public function getRelationType(LabelEventInterface $labelEvent);
}
