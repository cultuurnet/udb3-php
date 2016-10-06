<?php

namespace CultuurNet\UDB3\Organizer\Commands;

use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use CultuurNet\UDB3\Security\LabelSecurityInterface;
use ValueObjects\Identity\UUID;

abstract class AbstractLabelCommand extends AbstractOrganizerCommand implements AuthorizableCommandInterface, LabelSecurityInterface
{
    /**
     * @var UUID
     */
    private $labelId;

    /**
     * @param string $organizerId
     * @param UUID $labelId
     */
    public function __construct(
        $organizerId,
        UUID $labelId
    ) {
        parent::__construct($organizerId);
        $this->labelId = $labelId;
    }

    /**
     * @return UUID
     */
    public function getLabelId()
    {
        return $this->labelId;
    }

    /**
     * @inheritdoc
     */
    public function getItemId()
    {
        return $this->getOrganizerId();
    }

    /**
     * @inheritdoc
     */
    public function getPermission()
    {
        return Permission::AANBOD_BEWERKEN();
    }

    /**
     * @inheritdoc
     */
    public function isIdentifiedByUuid()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getUuid()
    {
        return $this->labelId;
    }
}