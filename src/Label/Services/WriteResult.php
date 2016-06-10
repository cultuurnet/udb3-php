<?php

namespace CultuurNet\UDB3\Label\Services;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class WriteResult implements \JsonSerializable
{
    const COMMAND_ID = 'commandId';
    const UUID = 'uuid';

    /**
     * @var StringLiteral
     */
    private $commandId;

    /**
     * @var UUID
     */
    private $uuid;

    /**
     * WriteResponse constructor.
     * @param StringLiteral $commandId
     * @param UUID $uuid
     */
    public function __construct(StringLiteral $commandId, UUID $uuid = null)
    {
        $this->commandId = $commandId;
        $this->uuid = $uuid;
    }

    /**
     * @return StringLiteral
     */
    public function getCommandId()
    {
        return $this->commandId;
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
    public function jsonSerialize()
    {
        return [
            self::COMMAND_ID => $this->commandId->toNative(),
            self::UUID => $this->uuid->toNative()
        ];
    }
}
