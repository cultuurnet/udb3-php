<?php

namespace CultuurNet\UDB3\Event\ReadModel\Permission;

use ValueObjects\String\String;

interface PermissionRepositoryInterface
{
    /**
     * @param String $eventId
     * @param String $uitId
     * @return void
     */
    public function markEventEditableByUser(String $eventId, String $uitId);
}
