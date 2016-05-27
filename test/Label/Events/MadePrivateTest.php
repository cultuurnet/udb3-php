<?php

namespace CultuurNet\UDB3\Label\Events;

use ValueObjects\Identity\UUID;

class MadePrivateTestAbstract extends AbstractExtendsTest
{
    /**
     * @inheritdoc
     */
    public function createEvent(UUID $uuid)
    {
        return new MadePrivate($uuid);
    }

    /**
     * @inheritdoc
     */
    public function deserialize(array $array)
    {
        return MadePrivate::deserialize(
            [AbstractEvent::UUID => $this->uuid->toNative()]
        );
    }
}
