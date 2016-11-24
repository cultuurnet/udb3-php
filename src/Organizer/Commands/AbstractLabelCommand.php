<?php

namespace CultuurNet\UDB3\Organizer\Commands;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use CultuurNet\UDB3\Security\LabelSecurityInterface;
use ValueObjects\String\String as StringLiteral;

abstract class AbstractLabelCommand extends AbstractOrganizerCommand implements AuthorizableCommandInterface, LabelSecurityInterface
{
    /**
     * @var Label
     */
    private $label;

    /**
     * @param string $organizerId
     * @param Label $label
     */
    public function __construct(
        $organizerId,
        Label $label
    ) {
        parent::__construct($organizerId);
        $this->label = $label;
    }

    /**
     * @return Label
     */
    public function getLabel()
    {
        return $this->label;
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
    public function getName()
    {
        return new StringLiteral((string) $this->label);
    }
}
