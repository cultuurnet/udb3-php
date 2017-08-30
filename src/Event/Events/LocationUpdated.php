<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Location\LocationId;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

class LocationUpdated extends AbstractEvent
{
    /**
     * @var LocationId
     */
    private $locationId;

    /**
     * @param string $eventId
     * @param LocationId $locationId
     */
    public function __construct(
        $eventId,
        LocationId $locationId
    ) {
        parent::__construct($eventId);

        $this->locationId = $locationId;
    }

    /**
     * @return LocationId
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return parent::serialize() + [
                'location_id' => $this->locationId->toNative(),
            ];
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['item_id'],
            new LocationId($data['location_id'])
        );
    }
}