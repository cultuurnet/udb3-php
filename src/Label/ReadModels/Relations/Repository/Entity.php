<?php

namespace CultuurNet\UDB3\Label\ReadModels\Relations\Repository;

use CultuurNet\UDB3\Label\ValueObjects\RelationType;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class Entity
{
    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var RelationType
     */
    private $relationType;

    /**
     * @var StringLiteral
     */
    private $relationId;

    /**
     * Entity constructor.
     * @param UUID $uuid
     * @param RelationType $relationType
     * @param StringLiteral $relationId
     */
    public function __construct(
        UUID $uuid,
        RelationType $relationType,
        StringLiteral $relationId
    ) {
        $this->uuid = $uuid;
        $this->relationType = $relationType;
        $this->relationId = $relationId;
    }

    /**
     * @return UUID
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return RelationType
     */
    public function getRelationType()
    {
        return $this->relationType;
    }

    /**
     * @return StringLiteral
     */
    public function getRelationId()
    {
        return $this->relationId;
    }
}
