<?php

namespace CultuurNet\UDB3\Event\ReadModel\Permission;

use ValueObjects\String\String;

interface PermissionQueryInterface
{
    /**
     * @param String $uitId
     * @return String[] A list of Event ids.
     */
    public function getEditableEvents(String $uitId);
}
