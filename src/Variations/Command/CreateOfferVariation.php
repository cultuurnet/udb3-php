<?php

namespace CultuurNet\UDB3\Variations\Command;

use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;

class CreateOfferVariation
{
    /**
     * @var OwnerId
     */
    private $ownerId;

    /**
     * @var Purpose
     */
    private $purpose;

    /**
     * @var Description
     */
    private $description;

    /**
     * @var IriOfferIdentifier
     */
    private $identifier;

    /**
     * @param IriOfferIdentifier $identifier
     * @param OwnerId $ownerId
     * @param Purpose $purpose
     * @param Description $description
     */
    public function __construct(
        IriOfferIdentifier $identifier,
        OwnerId $ownerId,
        Purpose $purpose,
        Description $description
    ) {
        $this->identifier = $identifier;
        $this->ownerId = $ownerId;
        $this->purpose = $purpose;
        $this->description = $description;
    }

    /**
     * @return Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return IriOfferIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return OwnerId
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @return Purpose
     */
    public function getPurpose()
    {
        return $this->purpose;
    }
}
