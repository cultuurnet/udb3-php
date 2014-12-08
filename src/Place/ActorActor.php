<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Place\ActorActor.
 */

namespace CultuurNet\UDB3\Place;

use Broadway\Serializer\SerializableInterface;

abstract class ActorActor implements SerializableInterface
{
    protected $actorId;

    public function __construct($actorId)
    {
        $this->actorId = $actorId;
    }

    public function getActorId()
    {
        return $this->actorId;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'actor_id' => $this->actorId,
        );
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new static($data['actor_id']);
    }
}
