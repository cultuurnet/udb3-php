<?php

namespace CultuurNet\UDB3\Label\Events;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class MadePublicTestAbstract extends AbstractExtendsTest
{
    /**
     * @inheritdoc
     */
    public function createEvent(UUID $uuid, StringLiteral $name)
    {
        return new MadePublic($uuid, $name);
    }

    /**
     * @inheritdoc
     */
    public function deserialize(array $array)
    {
        return MadePublic::deserialize(
            [
                'uuid' => $this->uuid->toNative(),
                'name' => $this->name->toNative(),
            ]
        );
    }
}
