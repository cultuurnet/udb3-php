<?php

namespace CultuurNet\UDB3\Place\Commands;

use CultuurNet\UDB3\Offer\Commands\AbstractCommand;
use CultuurNet\UDB3\Role\ValueObjects\Permission;

class UpdateFacilities extends AbstractCommand
{
    /**
     * Facilities to be added.
     * @var array
     */
    protected $facilities;

    /**
     * @param string $itemId
     * @param array $facilities
     */
    public function __construct($itemId, array $facilities)
    {
        parent::__construct($itemId);
        $this->facilities = $facilities;
    }

    /**
     * @return array
     */
    public function getFacilities()
    {
        return $this->facilities;
    }

    /**
     * @inheritdoc
     */
    public function getPermission()
    {
        return Permission::VOORZIENINGEN_BEWERKEN();
    }
}
