<?php

/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 27.10.15
 * Time: 10:24
 */

namespace CultuurNet\UDB3\Event\ReadModel\Permission;

interface PermissionRepositoryInterface
{
    public function getEditableEvents($uitid, $email);
}
