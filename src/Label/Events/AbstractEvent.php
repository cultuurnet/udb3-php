<?php

namespace CultuurNet\UDB3\Label\Events;

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
        return new static($data[self::UUID]);
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return [self::UUID => $this->getUuid()];
    }
}
