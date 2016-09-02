<?php

namespace CultuurNet\UDB3\Label\Events;

use ValueObjects\Identity\UUID;

class MadePublicTestAbstract extends AbstractExtendsTest
{
    /**
     * @inheritdoc
     */
    public function createEvent(UUID $uuid)
    {
        return new MadePublic($uuid);
    }

    /**
     * @inheritdoc
     */
    public function deserialize(array $array)
    {
        return MadePublic::deserialize(
            [AbstractEvent::UUID => $this->uuid->toNative()]
        );
    }
}
