<?php

namespace CultuurNet\UDB3\Label\Events;

use ValueObjects\Identity\UUID;

class MadePrivateTest extends ExtendsTest
{
    /**
     * @inheritdoc
     */
    public function createEvent(UUID $uuid)
    {
        return new MadePrivate($uuid);
    }
}
