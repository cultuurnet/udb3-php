<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\Actor;

interface ActorSpecification
{
    /**
     * @param \CultureFeed_Cdb_Item_Actor $actor
     * @return bool
     */
    public function isSatisfiedBy(\CultureFeed_Cdb_Item_Actor $actor);
}
