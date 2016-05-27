<?php

namespace CultuurNet\UDB3\Label\Events;

use ValueObjects\Identity\UUID;

class MadeVisibleTestAbstract extends AbstractExtendsTest
{
    /**
     * @inheritdoc
     */
    public function createEvent(UUID $uuid)
    {
        return new MadeVisible($uuid);
    }

    /**
     * @inheritdoc
     */
    public function deserialize(array $array)
    {
        return MadeVisible::deserialize(
            [AbstractEvent::UUID => $this->uuid->toNative()]
        );
    }
}
