<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\Actor\ActorEvent.
 */

namespace CultuurNet\UDB3\Actor;

use Broadway\Serializer\SerializableInterface;

abstract class ActorEvent implements SerializableInterface
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
     * {@inheritdoc}
     */
    public function serialize()
    {
        return array(
            'actor_id' => $this->actorId,
        );
    }
}
