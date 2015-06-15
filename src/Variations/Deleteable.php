<?php

namespace CultuurNet\UDB3\Variations;

interface Deleteable
{
    /**
     * @return boolean
     */
    public function isDeleted();

    /**
     * @throws AggregateDeletedException
     */
    public function markDeleted();
}
