<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\ReadModel\Search;

use CultuurNet\UDB3\Variations\Model\EventVariation;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;

class Criteria
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
     * @var Url
     */
    private $eventUrl;

    public function withOwnerId(OwnerId $ownerId)
    {
        $new = clone $this;
        $new->setOwnerId($ownerId);

        return $new;
    }

    public function withPurpose(Purpose $purpose)
    {
        $new = clone $this;
        $new->setPurpose($purpose);

        return $new;
    }

    public function withEventUrl(Url $eventUrl)
    {
        $new = clone $this;
        $new->setEventUrl($eventUrl);
        return $new;
    }

    /**
     * @param Url $eventUrl
     */
    private function setEventUrl(Url $eventUrl)
    {
        $this->eventUrl = $eventUrl;
    }

    /**
     * @param OwnerId $ownerId
     */
    private function setOwnerId(OwnerId $ownerId)
    {
        $this->ownerId = $ownerId;
    }

    /**
     * @param Purpose $purpose
     */
    public function setPurpose(Purpose $purpose)
    {
        $this->purpose = $purpose;
    }

    /**
     * @return Url
     */
    public function getEventUrl()
    {
        return $this->eventUrl;
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

    /**
     * @param EventVariation $variation
     * @return bool
     */
    public function isSatisfiedBy(EventVariation $variation)
    {
        $satisfied = true;

        if (($this->purpose && $variation->getPurpose() != $this->purpose) ||
            ($this->ownerId && $variation->getOwnerId() != $this->ownerId) ||
            ($this->eventUrl && $variation->getEventUrl() != $this->eventUrl)
        ) {
            $satisfied = false;
        }

        return $satisfied;
    }
}
