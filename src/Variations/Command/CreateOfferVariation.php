<?php
/**
 * @file
 */

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
    private $offerUrl;

    /**
     * @param Url $eventUrl
     * @param OwnerId $ownerId
     * @param Purpose $purpose
     * @param Description $description
     */
    public function __construct(
        Url $offerUrl,
        OwnerId $ownerId,
        Purpose $purpose,
        Description $description
    ) {
        $this->offerUrl = $offerUrl;
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
    public function getOfferUrl()
    {
        return $this->offerUrl;
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
