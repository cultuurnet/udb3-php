<?php

namespace CultuurNet\UDB3\Label\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use ValueObjects\Identity\UUID;

abstract class AbstractEvent implements SerializableInterface
{
    const UUID = 'uuid';
    const NAME = 'name';

    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var LabelName
     */
    private $name;

    /**
     * AbstractEvent constructor.
     * @param UUID $uuid
     * @param LabelName $name
     */
    public function __construct(UUID $uuid, LabelName $name)
    {
        $this->uuid = $uuid;
        $this->name = $name;
    }

    /**
     * @return UUID
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return LabelName
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        return new static(
            new UUID($data[self::UUID]),
            new LabelName($data[self::NAME])
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return [
            self::UUID => $this->getUuid()->toNative(),
            self::NAME => $this->getName()->toNative(),
        ];
    }
}
