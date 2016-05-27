<?php

namespace CultuurNet\UDB3\Label\Events;

use ValueObjects\Identity\UUID;

class MadeInvisibleTestAbstract extends AbstractExtendsTest
{
    /**
     * @inheritdoc
     */
    public function createEvent(UUID $uuid)
    {
        return new MadeInvisible($uuid);
    }

    /**
     * @inheritdoc
     */
    public function deserialize(array $array)
    {
        return MadeInvisible::deserialize(
            [AbstractEvent::UUID => $this->uuid->toNative()]
        );
    }
}
