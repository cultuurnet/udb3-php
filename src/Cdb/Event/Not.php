<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Cdb\Event;

use CultureFeed_Cdb_Item_Event;

class Not implements SpecificationInterface
{
    /**
     * @var SpecificationInterface
     */
    private $spec;

    function __construct(SpecificationInterface $spec)
    {
        $this->spec = $spec;
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedByEvent(CultureFeed_Cdb_Item_Event $event)
    {
        return !$this->spec->isSatisfiedByEvent($event);
    }

}
