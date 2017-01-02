<?php

namespace CultuurNet\UDB3\Event\Commands;

use CultuurNet\UDB3\Event\ValueObjects\AudienceType;
use CultuurNet\UDB3\Offer\Commands\AbstractCommand;

class UpdateAudience extends AbstractCommand
{
    /**
     * @var AudienceType
     */
    private $audienceType;

    /**
     * UpdateAudience constructor.
     * @param string $itemId
     * @param AudienceType $audienceType
     */
    public function __construct(
        $itemId,
        AudienceType $audienceType
    ) {
        parent::__construct($itemId);

        $this->audienceType = $audienceType;
    }

    /**
     * @return AudienceType
     */
    public function getAudienceType()
    {
        return $this->audienceType;
    }
}
