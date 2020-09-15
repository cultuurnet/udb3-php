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
    public function getUuid(): UUID
    {
        return $this->uuid;
    }

    /**
     * @return LabelName
     */
    public function getName(): LabelName
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function serialize(): array
    {
        return [
            self::UUID => $this->getUuid()->toNative(),
            self::NAME => $this->getName()->toNative(),
        ];
    }
}
