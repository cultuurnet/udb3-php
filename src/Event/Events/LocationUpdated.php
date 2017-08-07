<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

class LocationUpdated extends AbstractEvent
{
    /**
     * @var Location
     */
    private $location;

    /**
     * LocationUpdated constructor.
     * @param string $eventId
     * @param Location $location
     */
    public function __construct(
        $eventId,
        Location $location
    ) {
        parent::__construct($eventId);

        $this->location = $location;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return parent::serialize() + [
                'location' => $this->location->serialize(),
            ];
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['item_id'],
            Location::deserialize($data['location'])
        );
    }
}
