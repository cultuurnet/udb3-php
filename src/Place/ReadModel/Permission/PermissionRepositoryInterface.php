<?php

namespace CultuurNet\UDB3\Place\ReadModel\Permission;

use ValueObjects\String\String;

interface PermissionRepositoryInterface
{
    /**
     * @param String $eventId
     * @param String $uitId
     * @return void
     */
    public function markPlaceEditableByUser(String $placeId, String $uitId);
}
