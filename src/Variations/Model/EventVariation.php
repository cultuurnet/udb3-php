<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Model;

use CultuurNet\UDB3\Variations\Model\Events\EventVariationCreated;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;

class EventVariation extends \Broadway\EventSourcing\EventSourcedAggregateRoot
{
    public static function create(
        Id $id,
        Url $eventUrl,
        Purpose $purpose,
        OwnerId $ownerId,
        Description $description
    )
    {
        $variation = new static();
        $variation->apply(
            new EventVariationCreated(
                $id,
                $eventUrl,
                $ownerId,
                $purpose,
                $description
            )
        );

        return $variation;
    }

    /**
     * @return string
     */
    public function getAggregateRootId()
    {
        return $this->id;
    }

}
