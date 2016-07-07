<?php

namespace CultuurNet\UDB3\Role\Events;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class RoleRenamed extends AbstractEvent
{
    /**
     * @var StringLiteral
     */
    private $name;

    /**
     * RoleCreated constructor.
     * @param UUID $uuid
     * @param StringLiteral $name
     */
    public function __construct(UUID $uuid, StringLiteral $name)
    {
        parent::__construct($uuid);
        $this->name = $name;
    }

    /**
     * @return StringLiteral
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        return new static(
            new UUID($data['uuid']),
            new StringLiteral($data['name'])
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return parent::serialize() + [
            'name' => $this->name->toNative()
        ];
    }
}
