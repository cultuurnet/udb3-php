<?php

namespace CultuurNet\UDB3\Role\Events;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String as stringLiteral;

class AbstractConstraintEvent extends AbstractEvent
{
    /**
     * @var StringLiteral
     */
    private $query;

    /**
     * AbstractPermissionEvent constructor.
     * @param UUID $uuid
     * @param StringLiteral $query
     */
    public function __construct(UUID $uuid, StringLiteral $query)
    {
        parent::__construct($uuid);
        $this->query = $query;
    }

    /**
     * @return StringLiteral
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data)
    {
        return new static($data['uuid'], new StringLiteral($data['query']));
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'query' => $this->query->toNative(),
        );
    }
}
