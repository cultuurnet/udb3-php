<?php

namespace CultuurNet\UDB3\Label\Events;

use Broadway\Serializer\SerializableInterface;
use ValueObjects\Identity\UUID;

class LabelDetailsProjectedToJSONLD implements SerializableInterface
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
        return new static(
            new UUID($data[self::UUID])
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return [
            self::UUID => $this->getUuid()->toNative(),
        ];
    }
}
