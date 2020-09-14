<?php

namespace CultuurNet\UDB3\Event\Events;

use CultuurNet\UDB3\Event\ValueObjects\LocationId;
use CultuurNet\UDB3\Offer\Events\AbstractEvent;

final class LocationUpdated extends AbstractEvent
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
        return new self(
            $data['item_id'],
            new LocationId($data['location_id'])
        );
    }
}
