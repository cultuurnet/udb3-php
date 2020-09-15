<?php
/**
 * @file
 * Contains CultuurNet\UDB3\Place\PlaceEvent.
 */

namespace CultuurNet\UDB3\Place;

use Broadway\Serializer\SerializableInterface;

/**
 * Abstract class for events on places.
 */
abstract class PlaceEvent implements SerializableInterface
{
    /**
     * @var string
     */
    protected $placeId;

    public function __construct(string $placeId)
    {
        $this->placeId = $placeId;
    }

    public function getPlaceId(): string
    {
        return $this->placeId;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return array(
            'place_id' => $this->placeId,
        );
    }
}
