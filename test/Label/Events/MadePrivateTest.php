<?php

namespace CultuurNet\UDB3\Label\Events;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class MadePrivateTestAbstract extends AbstractExtendsTest
{
    /**
     * @inheritdoc
     */
    public function createEvent(UUID $uuid, StringLiteral $name)
    {
        return new MadePrivate($uuid, $name);
    }

    /**
     * @inheritdoc
     */
    public function deserialize(array $array)
    {
        return MadePrivate::deserialize(
            [
                'uuid' => $this->uuid->toNative(),
                'name' => $this->name->toNative(),
            ]
        );
    }
}
