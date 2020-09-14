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
    protected $placeId;

    public function __construct($placeId)
    {
        $this->placeId = $placeId;
    }

    public function getPlaceId()
    {
        return $this->placeId;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'place_id' => $this->placeId,
        );
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['place_id']);
    }
}
