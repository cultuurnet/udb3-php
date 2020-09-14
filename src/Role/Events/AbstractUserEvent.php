<?php

namespace CultuurNet\UDB3\Role\Events;

use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

abstract class AbstractUserEvent extends AbstractEvent
{
    public const USER_ID = 'userId';

    /**
     * @var StringLiteral
     */
    private $userId;

    /**
     * AbstractUserEvent constructor.
     * @param UUID $uuid
     * @param StringLiteral $userId
     */
    final public function __construct(UUID $uuid, StringLiteral $userId)
    {
        parent::__construct($uuid);

        $this->userId = $userId;
    }

    /**
     * @return StringLiteral
     */
    public function getUserId(): StringLiteral
    {
        return $this->userId;
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data): AbstractUserEvent
    {
        return new static(
            new UUID($data[self::UUID]),
            new StringLiteral($data[self::USER_ID])
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize(): array
    {
        return parent::serialize() + [self::USER_ID => $this->getUserId()->toNative()];
    }
}
