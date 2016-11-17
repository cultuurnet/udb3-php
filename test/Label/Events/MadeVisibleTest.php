<?php

namespace CultuurNet\UDB3\Label\Events;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class MadeVisibleTestAbstract extends AbstractExtendsTest
{
    /**
     * @inheritdoc
     */
    public function createEvent(UUID $uuid, StringLiteral $name)
    {
        return new MadeVisible($uuid, $name);
    }

    /**
     * @inheritdoc
     */
    public function deserialize(array $array)
    {
        return MadeVisible::deserialize(
            [
                'uuid' => $this->uuid->toNative(),
                'name' => $this->name->toNative(),
            ]
        );
    }
}
