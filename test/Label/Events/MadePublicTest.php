<?php

namespace CultuurNet\UDB3\Label\Events;

use ValueObjects\Identity\UUID;

class MadePublicTest extends ExtendsTest
{
    /**
     * @inheritdoc
     */
    public function createEvent(UUID $uuid)
    {
        return new MadePublic($uuid);
    }
}
