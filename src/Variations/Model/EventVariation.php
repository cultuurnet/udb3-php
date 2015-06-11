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
    /**
     * @var Id
     */
    private $id;

    /**
     * @param Id $id
     * @param Url $eventUrl
     * @param Purpose $purpose
     * @param OwnerId $ownerId
     * @param Description $description
     * @return static
     */
    public static function create(
        Id $id,
        Url $eventUrl,
        OwnerId $ownerId,
        Purpose $purpose,
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

    protected function applyEventVariationCreated(EventVariationCreated $eventVariationCreated)
    {
        $this->id = $eventVariationCreated->getId();
    }

    /**
     * @return string
     */
    public function getAggregateRootId()
    {
        return (string) $this->id;
    }

}
