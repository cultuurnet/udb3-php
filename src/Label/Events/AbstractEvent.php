<?php

namespace CultuurNet\UDB3\Label\Events;

use Broadway\Serializer\SerializableInterface;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

abstract class AbstractEvent implements SerializableInterface
{
    const UUID = 'uuid';
    const NAME = 'name';

    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var StringLiteral
     */
    private $name;

    /**
     * AbstractEvent constructor.
     * @param UUID $uuid
     * @param StringLiteral $name
     */
    public function __construct(UUID $uuid, StringLiteral $name)
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
     * @return StringLiteral
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
            new StringLiteral($data[self::NAME])
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
