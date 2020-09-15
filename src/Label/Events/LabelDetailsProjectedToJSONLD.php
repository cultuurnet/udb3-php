<?php

namespace CultuurNet\UDB3\Label\Events;

use Broadway\Serializer\SerializableInterface;
use ValueObjects\Identity\UUID;

final class LabelDetailsProjectedToJSONLD implements SerializableInterface
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
    public function getUuid(): UUID
    {
        return $this->uuid;
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data): LabelDetailsProjectedToJSONLD
    {
        return new static(
            new UUID($data[self::UUID])
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize(): array
    {
        return [
            self::UUID => $this->getUuid()->toNative(),
        ];
    }
}
