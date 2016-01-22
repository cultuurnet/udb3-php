<?php

namespace CultuurNet\UDB3;

use TwoDotsTwice\Collection\AbstractCollection;

class CollaborationDataCollection extends AbstractCollection
{
    /**
     * @return string
     */
    protected function getValidObjectType()
    {
        return CollaborationData::class;
    }
}
