<?php

namespace CultuurNet\UDB3\Organizer\Commands;

use CultuurNet\UDB3\Role\ValueObjects\Permission;

class DeleteOrganizer extends AbstractOrganizerCommand
{
    /**
     * @inheritdoc
     */
    public function getPermission()
    {
        return Permission::ORGANISATIES_BEHEREN();
    }
}
