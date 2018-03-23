<?php

namespace CultuurNet\UDB3\Organizer\Commands;

use CultuurNet\UDB3\Model\ValueObject\Taxonomy\Label\Label;
use CultuurNet\UDB3\Model\ValueObject\Taxonomy\Label\Labels;
use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use CultuurNet\UDB3\Security\LabelSecurityInterface;
use ValueObjects\StringLiteral\StringLiteral;

class ImportLabels extends AbstractOrganizerCommand implements AuthorizableCommandInterface, LabelSecurityInterface
{
    /**
     * @var Labels
     */
    private $labels;

    /**
     * @param string $organizerId
     * @param Labels $label
     */
    public function __construct(
        $organizerId,
        Labels $label
    ) {
        parent::__construct($organizerId);
        $this->labels = $label;
    }

    /**
     * @return Labels
     */
    public function getLabels()
    {
        return $this->labels;
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
    public function getNames()
    {
        return array_map(
            function (Label $label) {
                return new StringLiteral($label->getName()->toString());
            },
            $this->getLabels()->toArray()
        );
    }
}