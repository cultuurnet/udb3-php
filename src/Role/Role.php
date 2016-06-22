<?php

namespace CultuurNet\UDB3\Role;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class Role extends EventSourcedAggregateRoot
{
    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var StringLiteral
     */
    private $name;

    /**
     * @return string
     */
    public function getAggregateRootId()
    {
        return $this->uuid;
    }
}
