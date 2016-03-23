<?php

namespace CultuurNet\UDB3\Variations\Command;

use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;

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
     * @var Url
     */
    private $originUrl;

    /**
     * @param Url $originUrl
     * @param OwnerId $ownerId
     * @param Purpose $purpose
     * @param Description $description
     */
    public function __construct(
        Url $originUrl,
        OwnerId $ownerId,
        Purpose $purpose,
        Description $description
    ) {
        $this->originUrl = $originUrl;
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
     * @return Url
     */
    public function getOriginUrl()
    {
        return $this->originUrl;
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
