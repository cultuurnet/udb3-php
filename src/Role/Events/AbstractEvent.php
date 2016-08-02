<?php

namespace CultuurNet\UDB3\Role\Events;

use Broadway\Serializer\SerializableInterface;
use ValueObjects\Identity\UUID;

abstract class AbstractEvent implements SerializableInterface
{
    const UUID = 'uuid';

    /**
     * @var UUID
     */
    private $uuid;

    /**
     * AbstractEvent constructor.
     * @param UUID $uuid
     */
    public function __construct(UUID $uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return UUID
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        return new static(new UUID($data['uuid']));
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return ['uuid' => $this->getUuid()->toNative()];
    }
}
