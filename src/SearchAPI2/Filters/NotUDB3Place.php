<?php

namespace CultuurNet\UDB3\SearchAPI2\Filters;

/**
 * @consideredForRemoval
 *   This class is no longer being used as of 08-01-2016.
 *   Unless there are new usages, we should consider removing it.
 */
class NotUDB3Place extends DoesNotHaveKeyword
{
    public function __construct()
    {
        parent::__construct('udb3 place');
    }
}
